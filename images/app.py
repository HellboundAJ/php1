import os
import jwt
import datetime
import requests
import secrets
import email.utils
import uuid
import re
from functools import wraps
from urllib.parse import urlparse
import tldextract

from flask import Flask, request, render_template, redirect, url_for, make_response, jsonify, flash
from flask_sqlalchemy import SQLAlchemy
from werkzeug.security import generate_password_hash, check_password_hash
from werkzeug.utils import secure_filename

from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.chrome.service import Service

app = Flask(__name__)
app.config['SECRET_KEY'] = os.environ.get('SECRET_KEY', 'supersecret_redacted_key')
app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///managely.db'
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False
app.config['MAX_CONTENT_LENGTH'] = 1 * 1024 * 1024  # 1MB limit

db = SQLAlchemy(app)

# --- Models ---
class User(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    username = db.Column(db.String(80), unique=True, nullable=False)
    email = db.Column(db.String(120), unique=True, nullable=False)
    password = db.Column(db.String(255), nullable=False)
    role = db.Column(db.String(20), default='customer')
    bio = db.Column(db.String(500), nullable=True)

class Account(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    username = db.Column(db.String(80), unique=True, nullable=False)
    email = db.Column(db.String(120), unique=True, nullable=False)
    link = db.Column(db.String(255), nullable=False)
    bio = db.Column(db.String(500), nullable=True)

class Company(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    name = db.Column(db.String(100), nullable=False)
    logo_path = db.Column(db.String(200), nullable=True)

# --- Helpers ---
def token_required(f):
    @wraps(f)
    def decorated(*args, **kwargs):
        token = request.cookies.get('auth_token')
        if not token:
            return redirect(url_for('login'))
        try:
            data = jwt.decode(token, app.config['SECRET_KEY'], algorithms=["HS256"])
            current_user = User.query.filter_by(id=data['user_id']).first()
            if not current_user:
                return redirect(url_for('login'))
            
            if 'role' in data:
                current_user.role = data['role']
                
        except Exception as e:
            return redirect(url_for('login'))
        return f(current_user, *args, **kwargs)
    return decorated

def admin_required(f):
    @wraps(f)
    def decorated(current_user, *args, **kwargs):
        if current_user.role != 'admin':
            return render_template('base.html', content="<h1>403 Forbidden</h1><p>Admins only.</p>"), 403
        return f(current_user, *args, **kwargs)
    return decorated

def init_db():
    db.create_all()
    if not User.query.filter_by(username='admin').first():
        admin_pass = secrets.token_hex(16)
        admin = User(
            username='admin',
            email='admin@admin.managely.social',
            password=generate_password_hash(admin_pass),
            role='admin',
            bio="System Administrator"
        )
        db.session.add(admin)
    
    if not Account.query.filter_by(username='John_Doe').first():
        vip1 = Account(
            username='John_Doe',
            email='john@corporation.com',
            link='https://instagram/John_Doe',
            bio=os.environ.get('FLAG_PART_1', 'test_part1')
        )
        vip2 = Account(
            username='Abdelkhalek Beraoud',
            email='beraoud@cybersecurity.com',
            link='https://instagram/beraoud',
            bio="Cybersecurity Enthusiast"
        )
        db.session.add(vip1)
        db.session.add(vip2)
        
    db.session.commit()

# --- Routes ---

@app.route('/')
def index():
    return redirect(url_for('login'))

# For now only admins can login
@app.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        email_addr = request.form.get('email')
        password = request.form.get('password')
        
        user = User.query.filter_by(email=email_addr).first()
        
        if user and check_password_hash(user.password, password):
            user_role = user.role
            
            try:
                domain = email_addr.split('@')[-1]
                if domain == 'admin.managely.social':
                    user_role = 'admin'
            except:
                pass

            if user_role == 'admin':
                token = jwt.encode({
                    'user_id': user.id,
                    'role': user_role,
                    'exp': datetime.datetime.utcnow() + datetime.timedelta(hours=72)
                }, app.config['SECRET_KEY'], algorithm="HS256")
                
                resp = make_response(redirect(url_for('dashboard')))
                resp.set_cookie('auth_token', token)
                return resp
        
        flash("Invalid credentials", "danger")
    return render_template('index.html')

# A feature to create new admin accounts: TODO
# @app.route('/create_admin', methods=['GET', 'POST'])
# @token_required
# @admin_required

# A feature to create new marketing compaigns: TODO
# @app.route('/create_compaign', methods=['GET', 'POST'])
# @token_required
# @marketer_required

@app.route('/register', methods=['GET', 'POST'])
def register():
    if request.method == 'POST':
        username = request.form.get('username')
        email_addr = request.form.get('email')
        password = request.form.get('password')

        _, addr = email.utils.parseaddr(email_addr)
        email_regex = r'^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$'

        if not addr:
            return render_template('register.html', error="No email address provided")
        
        if not bool(re.match(email_regex, addr)):
            return render_template('register.html', error="Invalid email address")
        
        if "admin.managely.social" in addr:
            return render_template('register.html', error="Registration restricted. Please use the created system admin account")
        
        if User.query.filter_by(username=username).first():
            return render_template('register.html', error="Username taken")
        
        if User.query.filter_by(email=email_addr).first():
            return render_template('register.html', error="email taken")

        new_user = User(
            username=username,
            email=email_addr, 
            password=generate_password_hash(password),
            role='customer'
        )
        db.session.add(new_user)
        db.session.commit()
        
        flash("Registration successful, but the customer portal is under maintenance. You will receive an invitations link by the next week", "warning")
        return redirect(url_for('login'))
        
    return render_template('register.html')

@app.route('/dashboard')
@token_required
@admin_required
def dashboard(current_user):
    companies = Company.query.limit(25).all()
    return render_template('dashboard.html', user=current_user, companies=companies)

@app.route('/add_company', methods=['POST'])
@token_required
@admin_required
def add_company(current_user):
    name = request.form.get('name')
    file = request.files.get('logo')
    
    orig_name = secure_filename(file.filename)
    ext = os.path.splitext(orig_name)[1].lower()
    new_name = f"{uuid.uuid4().hex}{ext}"
    file.save(os.path.join('uploads', new_name))
        
    new_comp = Company(name=name, logo_path=new_name)
    db.session.add(new_comp)
    db.session.commit()
    return redirect(url_for('dashboard'))

# A tool to check internet links and get the response
@app.route('/check_link', methods=['POST'])
@token_required
@admin_required
def check_link(current_user):

    url = request.form.get('url')

    if 'localhost' in url:
        return jsonify({"error": "Security Alert: 'localhost' is banned."}), 403
    
    blocked_patterns = [
        r'127\.', r'169\.254\.', r'\[', r'\]', r'::',
        
        r'0+\.0+\.0+\.0+',
        
        r'://0+(?::|/|$)',

        r'0x',               
        r'://0\d+',       
        r'\.0\d+',            
        
        r'://\d+(?:$|/|:)', 
        
        r'nip\.io', r'xip\.io', r'sslip\.io', r'lvh\.me', 
        r'localtest\.me', r'burpcollaborator', r'requestbin'
    ]

    for pattern in blocked_patterns:
        if re.search(pattern, url):
            return jsonify({
                "error": "Security Alert: Malicious IP format detected.",
            }), 403

    try:
        r = requests.get(url, timeout=5, allow_redirects=False)

        return jsonify({"status": r.status_code, "content": r.text[:1000]})
    except Exception as e:
        return jsonify({"error": str(e)})

# Search for VIP accounts
@app.route('/internal/vip_search')
def vip_search():
    if request.remote_addr != '127.0.0.1':
        return "Access Denied.", 403

    query = request.args.get('q', '')

    if not query:
        return "No query"
    
    # John Doe's information is top secret, should only be accessed from the database

    pattern = re.compile(r'^(?!.*John_Doe).*', flags=re.IGNORECASE)

    if not re.match(pattern, query):
        return "User not found", 403
    
    results = []
    accounts = Account.query.all()
    for acc in accounts:
        if acc.username:
            if acc.username in query:
                results.append(f"User: {acc.username} | Bio: {acc.bio}")
                break
    
    return "\n".join(results)

TEST_ACCOUNT=os.environ.get('FLAG_PART_2', 'test_part2') # second part of the flag

def run_selenium(target_url):
    print(f"[+] Launching Bot for: {target_url}")
    
    chrome_options = Options()
    chrome_options.add_argument("--headless")
    chrome_options.add_argument("--no-sandbox")
    chrome_options.add_argument("--disable-dev-shm-usage")
    chrome_options.add_argument("--disable-gpu")

    service = Service(executable_path="/usr/bin/chromedriver")
    driver = webdriver.Chrome(service=service, options=chrome_options)
    driver.set_page_load_timeout(7)

    title = "Error"
    try:
        driver.get(target_url)
        
        title = driver.title
    except Exception as e:
        print(f"[-] Error visiting {target_url}: {e}")
    finally:
        driver.quit()
        
    return title

# A social media chat bot still under development
# TODO: Auto reply, marketing DMs
# TODO: Facebook, X integration
@app.route('/social_media_bot', methods=['POST'])
@token_required
def visit_endpoint(current_user):
    data = request.get_json()
    if not data or 'url' not in data:
        return jsonify({"error": "No URL provided"}), 400

    url = data['url']
    
    final_url = url.replace("{username}", TEST_ACCOUNT)

    parsed_url= urlparse(final_url)
    print(parsed_url.scheme)
    if parsed_url.scheme not in ("http", "https"):
        return  jsonify({
        "status": "error", 
        "message": "only HTTP or HTTPS are allowed"
    })

    hostname = urlparse(url).hostname
    domain = tldextract.extract(hostname).domain
    if 'instagram' != domain:
        return  jsonify({
        "status": "error", 
        "message": "only Instagram links are allowed"
    })

    page_title = run_selenium(final_url)
    
    return jsonify({
        "status": "success", 
        "message": f"Admin visited {url}"
    })

if __name__ == '__main__':
    app.run(debug=False)
