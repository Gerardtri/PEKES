import os
import json
import sqlite3
from datetime import datetime
from flask import Flask, request, jsonify, render_template, send_from_directory
from flask_cors import CORS
from werkzeug.utils import secure_filename
from database import get_db, init_db

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
PROJECT_DIR = os.path.abspath(os.path.join(BASE_DIR, '..'))
UPLOAD_FOLDER = os.path.join(PROJECT_DIR, 'public', 'images', 'products')
ALLOWED_EXTENSIONS = {'png', 'jpg', 'jpeg', 'webp', 'svg'}

os.makedirs(UPLOAD_FOLDER, exist_ok=True)

app = Flask(__name__, template_folder=os.path.join(BASE_DIR, 'templates'))
app.config['UPLOAD_FOLDER'] = UPLOAD_FOLDER
app.config['MAX_CONTENT_LENGTH'] = 16 * 1024 * 1024 # 16 MB max

CORS(app)

def allowed_file(filename):
    return '.' in filename and filename.rsplit('.', 1)[1].lower() in ALLOWED_EXTENSIONS

def calculate_price_cop(catalog_price, factor=1.30):
    if not catalog_price:
        return 0
    raw = float(catalog_price) * float(factor)
    return int(round(raw / 1000.0) * 1000)

def product_row_to_dict(row):
    d = dict(row)
    if d.get('includes'):
        try:
            d['includes'] = json.loads(d['includes'])
        except:
            d['includes'] = [d['includes']]
    else:
        d['includes'] = []

    if d.get('tags'):
        try:
            d['tags'] = json.loads(d['tags'])
        except:
            d['tags'] = []
    else:
        d['tags'] = []

    d['is_render'] = bool(d.get('is_render'))
    d['is_pending'] = bool(d.get('is_pending'))
    return d

# ==============================================================================
# API Endpoints
# ==============================================================================

@app.route('/api/products', methods=['GET'])
def get_products():
    category = request.args.get('category')
    search = request.args.get('search')
    stage = request.args.get('stage')

    conn = get_db()
    cursor = conn.cursor()

    query = 'SELECT * FROM products WHERE 1=1'
    params = []

    if category and category != 'all':
        query += ' AND category = ?'
        params.append(category)

    if stage:
        query += ' AND stage = ?'
        params.append(int(stage))

    if search:
        s = f'%{search.strip().lower()}%'
        query += ' AND (LOWER(name) LIKE ? OR LOWER(sku) LIKE ? OR LOWER(dimensions) LIKE ?)'
        params.extend([s, s, s])

    query += ' ORDER BY stage ASC, page ASC, id ASC'
    cursor.execute(query, params)
    rows = cursor.fetchall()
    conn.close()

    products = [product_row_to_dict(r) for r in rows]
    return jsonify(products)

@app.route('/api/products/<product_id>', methods=['GET'])
def get_product(product_id):
    conn = get_db()
    cursor = conn.cursor()
    cursor.execute('SELECT * FROM products WHERE id = ?', (product_id,))
    row = cursor.fetchone()
    conn.close()

    if not row:
        return jsonify({'error': 'Producto no encontrado'}), 404

    return jsonify(product_row_to_dict(row))

@app.route('/api/products', methods=['POST'])
def create_product():
    data = request.json or {}
    name = data.get('name', '').strip()
    category = data.get('category', 'Gimnasios de piso')
    catalog_price = data.get('catalog_price')
    sku = data.get('sku', '').strip()

    if not name:
        return jsonify({'error': 'El nombre del producto es obligatorio'}), 400

    conn = get_db()
    cursor = conn.cursor()

    # Generate SKU if empty
    if not sku:
        cursor.execute('SELECT COUNT(*) FROM products WHERE category = ?', (category,))
        cnt = cursor.fetchone()[0] + 1
        prefix = 'PIS' if 'piso' in category.lower() else ('PAR' if 'pared' in category.lower() else 'RAS')
        sku = f'PEK-{prefix}-{cnt:03d}'

    # Generate ID
    p_id = secure_filename(name.lower().replace(' ', '-'))
    cursor.execute('SELECT id FROM products WHERE id = ?', (p_id,))
    if cursor.fetchone():
        p_id = f"{p_id}-{int(datetime.now().timestamp())}"

    # Calculate price
    custom_price = data.get('price')
    if custom_price is not None and int(custom_price) > 0:
        price = int(custom_price)
    else:
        price = calculate_price_cop(catalog_price)

    dimensions = data.get('dimensions', '')
    image = data.get('image', '/images/products/placeholder.jpeg')
    description = data.get('description', '')
    includes = json.dumps(data.get('includes', []))
    tags = json.dumps(data.get('tags', ['gatos']))
    stage = int(data.get('stage', 1))
    is_pending = 1 if data.get('is_pending') else 0
    is_render = 1 if data.get('is_render') else 0

    cursor.execute('''
    INSERT INTO products (id, sku, name, category, stage, catalog_price, price, dimensions, image, description, includes, is_render, is_pending, tags, page)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ''', (p_id, sku, name, category, stage, catalog_price, price, dimensions, image, description, includes, is_render, is_pending, tags, 99))

    conn.commit()
    conn.close()

    return jsonify({'success': True, 'id': p_id, 'sku': sku}), 201

@app.route('/api/products/<product_id>', methods=['PUT'])
def update_product(product_id):
    data = request.json or {}
    conn = get_db()
    cursor = conn.cursor()

    cursor.execute('SELECT * FROM products WHERE id = ?', (product_id,))
    row = cursor.fetchone()
    if not row:
        conn.close()
        return jsonify({'error': 'Producto no encontrado'}), 404

    name = data.get('name', row['name']).strip()
    category = data.get('category', row['category'])
    sku = data.get('sku', row['sku']).strip()
    catalog_price = data.get('catalog_price', row['catalog_price'])
    
    # Check custom price or recalculate
    if 'price' in data and data['price'] is not None:
        price = int(data['price'])
    elif catalog_price:
        price = calculate_price_cop(catalog_price)
    else:
        price = row['price']

    dimensions = data.get('dimensions', row['dimensions'])
    image = data.get('image', row['image'])
    description = data.get('description', row['description'])
    
    includes = json.dumps(data['includes']) if 'includes' in data else row['includes']
    tags = json.dumps(data['tags']) if 'tags' in data else row['tags']
    is_pending = 1 if data.get('is_pending') else 0
    is_render = 1 if data.get('is_render') else 0

    cursor.execute('''
    UPDATE products
    SET sku = ?, name = ?, category = ?, catalog_price = ?, price = ?, dimensions = ?, 
        image = ?, description = ?, includes = ?, is_render = ?, is_pending = ?, tags = ?, 
        updated_at = CURRENT_TIMESTAMP
    WHERE id = ?
    ''', (sku, name, category, catalog_price, price, dimensions, image, description, includes, is_render, is_pending, tags, product_id))

    conn.commit()
    conn.close()

    return jsonify({'success': True, 'id': product_id})

@app.route('/api/products/<product_id>', methods=['DELETE'])
def delete_product(product_id):
    conn = get_db()
    cursor = conn.cursor()
    cursor.execute('DELETE FROM products WHERE id = ?', (product_id,))
    conn.commit()
    conn.close()
    return jsonify({'success': True, 'message': 'Producto eliminado'})

@app.route('/api/upload', methods=['POST'])
def upload_file():
    if 'file' not in request.files:
        return jsonify({'error': 'No file part'}), 400
    file = request.files['file']
    if file.filename == '':
        return jsonify({'error': 'No selected file'}), 400
    if file and allowed_file(file.filename):
        # Format name
        prefix = secure_filename(request.form.get('product_name', 'product')).lower()
        ext = file.filename.rsplit('.', 1)[1].lower()
        filename = f"{prefix}_{int(datetime.now().timestamp())}.{ext}"
        filepath = os.path.join(app.config['UPLOAD_FOLDER'], filename)
        file.save(filepath)
        
        web_path = f"/images/products/{filename}"
        return jsonify({'success': True, 'url': web_path, 'filename': filename})
    return jsonify({'error': 'File type not allowed'}), 400

@app.route('/api/settings', methods=['GET', 'POST'])
def manage_settings():
    conn = get_db()
    cursor = conn.cursor()
    if request.method == 'POST':
        data = request.json or {}
        for k, v in data.items():
            cursor.execute('INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)', (k, str(v)))
        conn.commit()
        conn.close()
        return jsonify({'success': True})
    else:
        cursor.execute('SELECT key, value FROM settings')
        rows = cursor.fetchall()
        conn.close()
        settings = {r['key']: r['value'] for r in rows}
        return jsonify(settings)

# ==============================================================================
# Admin Dashboard Route
# ==============================================================================

@app.route('/admin')
def admin_panel():
    return render_template('admin.html')

if __name__ == '__main__':
    init_db()
    print('Starting PEKES Python Backend on http://localhost:5001')
    app.run(host='0.0.0.0', port=5001, debug=True)
