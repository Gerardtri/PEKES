import sqlite3
import json
import os
from datetime import datetime

DB_PATH = os.path.join(os.path.dirname(__file__), 'pekes.db')
DATA_PATH = os.path.join(os.path.dirname(__file__), '..', 'src', 'data', 'products.js')

def get_db():
    conn = sqlite3.connect(DB_PATH)
    conn.row_factory = sqlite3.Row
    return conn

def init_db():
    os.makedirs(os.path.dirname(DB_PATH), exist_ok=True)
    conn = get_db()
    cursor = conn.cursor()

    cursor.execute('''
    CREATE TABLE IF NOT EXISTS products (
        id TEXT PRIMARY KEY,
        sku TEXT UNIQUE,
        name TEXT NOT NULL,
        category TEXT NOT NULL,
        stage INTEGER DEFAULT 1,
        catalog_price INTEGER,
        price INTEGER,
        dimensions TEXT,
        image TEXT,
        description TEXT,
        includes TEXT,
        is_render INTEGER DEFAULT 0,
        is_pending INTEGER DEFAULT 0,
        tags TEXT,
        page INTEGER,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
    ''')

    cursor.execute('''
    CREATE TABLE IF NOT EXISTS settings (
        key TEXT PRIMARY KEY,
        value TEXT
    )
    ''')

    # Default settings
    cursor.execute("INSERT OR IGNORE INTO settings (key, value) VALUES ('whatsapp_number', '573001234567')")
    cursor.execute("INSERT OR IGNORE INTO settings (key, value) VALUES ('store_name', 'PEKES Pet Shop')")
    cursor.execute("INSERT OR IGNORE INTO settings (key, value) VALUES ('city', 'Cali, Colombia')")
    cursor.execute("INSERT OR IGNORE INTO settings (key, value) VALUES ('markup_factor', '1.30')")

    conn.commit()

    # Check if products already exist
    cursor.execute('SELECT COUNT(*) FROM products')
    count = cursor.fetchone()[0]
    
    if count == 0:
        print('Populating database with catalog products...')
        # Read from scratch/catalog_extracted.json or src/data/products.js
        json_path = '/Users/gerard/.gemini/antigravity-ide/brain/51780a05-2d62-431d-8513-faac32086e98/scratch/catalog_extracted.json'
        
        # Load the refined products list from scratch/gen_products_js.py
        # Or parse src/data/products.js
        if os.path.exists(json_path):
            with open(json_path, 'r', encoding='utf-8') as f:
                raw_items = json.load(f)
                
            # Floor details map
            floor_map = {
                'MELINA': {'sku': 'PEK-PIS-001', 'dims': 'Alto: 60 cm | Ancho: 50 cm | Fondo: 40 cm', 'img': '/images/products/P06_MELINA.jpeg', 'desc': 'Gimnasio compacto de piso con plataforma superior y poste rascador en fibra natural.'},
                'ROMA': {'sku': 'PEK-PIS-002', 'dims': 'Alto: 60 cm | Ancho: 40 cm | Fondo: 40 cm', 'img': '/images/products/P07_ROMA.jpeg', 'desc': 'Gimnasio de piso con base cuadrada, rascador central y bandeja de descanso.'},
                'AGATHA': {'sku': 'PEK-PIS-003', 'dims': 'Alto: 80 cm | Ancho: 42 cm | Fondo: 32 cm', 'img': '/images/products/P08_AGATHA.jpeg', 'desc': 'Estructura vertical media de descanso con dos niveles y poste en cabuya natural.'},
                'MATEO': {'sku': 'PEK-PIS-004', 'dims': 'Alto: 55 cm | Ancho: 60 cm | Fondo: 30 cm', 'img': '/images/products/P09_MATEO.jpeg', 'desc': 'Gimnasio de baja altura con túnel / refugio inferior y rascador.'},
                'ALELI': {'sku': 'PEK-PIS-005', 'dims': 'Alto: 60 cm | Ancho: 50 cm | Fondo: 40 cm', 'img': '/images/products/P10_ALELI.jpeg', 'desc': 'Gimnasio con casita cúbica inferior y plataforma acolchada superior.'},
                'ZAFIRO': {'sku': 'PEK-PIS-006', 'dims': 'Alto: 95 cm | Base: diámetro 60 cm', 'img': '/images/products/P11_ZAFIRO.jpeg', 'desc': 'Gimnasio de base circular con cesta cóncava tipo nido y rascador robusto.'},
                'RUFUS': {'sku': 'PEK-PIS-007', 'dims': 'Alto: 90 cm | Ancho: 50 cm | Fondo: 50 cm', 'img': '/images/products/P12_RUFUS.jpeg', 'desc': 'Gimnasio mediano con refugio cerrado y niveles escalonados.'},
                'SIMBA': {'sku': 'PEK-PIS-008', 'dims': 'Alto: 100 cm | Ancho: 50 cm | Fondo: 40 cm', 'img': '/images/products/P13_SIMBA.jpeg', 'desc': 'Estructura de 1 metro con casita de acceso redondo, niveles y juguete colgante.'},
                'ESMERALDA': {'sku': 'PEK-PIS-009', 'dims': 'Alto: 100 cm | Ancho: 50 cm | Fondo: 40 cm', 'img': '/images/products/P14_ESMERALDA.jpeg', 'desc': 'Gimnasio de dos niveles con casa inferior silueta gato y poste largo.'},
                'APOLO': {'sku': 'PEK-PIS-010', 'dims': 'Alto: 110 cm | Ancho: 60 cm | Fondo: 45 cm', 'img': '/images/products/P15_APOLO.jpeg', 'desc': 'Torre escalonada con casa semicerrada y tres niveles de observación.'},
                'SKY': {'sku': 'PEK-PIS-011', 'dims': 'Alto: 95 cm | Ancho: 70 cm | Fondo: 40 cm', 'img': '/images/products/P16_SKY.jpeg', 'desc': 'Gimnasio horizontal amplio con cama hamaca baja, rascador alto y mirador.'},
                'FENIX': {'sku': 'PEK-PIS-012', 'dims': 'Alto: 110 cm | Ancho: 60 cm | Fondo: 45 cm', 'img': '/images/products/P17_FENIX.jpeg', 'desc': 'Gimnasio con hamaca inferior, casa intermedia y mirador superior acolchado.'},
                'CANELA': {'sku': 'PEK-PIS-013', 'dims': 'Alto: 130 cm | Ancho: 60 cm | Fondo: 45 cm', 'img': '/images/products/P18_CANELA.jpeg', 'desc': 'Gimnasio de 1.30 m con hamaca colgante de descanso y mirador superior con reborde.'},
                'OSIRIS': {'sku': 'PEK-PIS-014', 'dims': 'Alto: 115 cm | Ancho: 60 cm | Fondo: 60 cm', 'img': '/images/products/P19_OSIRIS.jpeg', 'desc': 'Estructura cúbica amplia con rascadores dobles y plataformas amplias.'},
                'MORITA': {'sku': 'PEK-PIS-015', 'dims': 'Alto: 155 cm | Ancho: 60 cm | Fondo: 40 cm', 'img': '/images/products/P20_MORITA.jpeg', 'desc': 'Torre alta de 1.55 m con cilindro refugio y múltiples plataformas circulares.'},
                'ARCANO': {'sku': 'PEK-PIS-016', 'dims': 'Alto: 135 cm | Ancho: 70 cm | Fondo: 40 cm', 'img': '/images/products/P21_ARCANO.jpeg', 'desc': 'Gimnasio multinivel con casa intermedia y postes en cabuya de alta resistencia.'},
                'ARES': {'sku': 'PEK-PIS-017', 'dims': 'Alto: 115 cm | Ancho: 90 cm | Fondo: 40 cm', 'img': '/images/products/P22_ARES.jpeg', 'desc': 'Gimnasio ancho de 90 cm con hamaca, rampa y refugio integrado.'},
                'HONEY': {'sku': 'PEK-PIS-018', 'dims': 'Alto: 135 cm | Ancho: 100 cm | Fondo: 60 cm', 'img': '/images/products/P23_HONEY.jpeg', 'desc': 'Diseño temático floral con cojines tipo flor y cilindro de descanso.', 'isRender': 1},
                'RAFAEL': {'sku': 'PEK-PIS-019', 'dims': 'Alto: 165 cm | Ancho: 90 cm | Fondo: 40 cm', 'img': '/images/products/P24_RAFAEL.jpeg', 'desc': 'Gran árbol para gatos de 1.65 m con hamaca, casa central, mirador cuna y postes.'},
                'GARY': {'sku': 'PEK-PIS-020', 'dims': 'Alto: 160 cm | Ancho: 50 cm | Fondo: 40 cm', 'img': '/images/products/P25_GARY.jpeg', 'desc': 'Torre estilizada de 1.60 m diseñada para optimizar espacio vertical.'},
                'BALBOA': {'sku': 'PEK-PIS-021', 'dims': 'Alto: 170 cm | Ancho: 60 cm | Fondo: 45 cm', 'img': '/images/products/P26_BALBOA.jpeg', 'desc': 'Gimnasio completo de 1.70 m con dos casas cúbicas, escalera y hamaca.'},
                'CHRIS': {'sku': 'PEK-PIS-022', 'dims': 'Alto: 215 cm | Ancho: 40 cm | Fondo: 40 cm', 'img': '/images/products/P27_CHRIS.jpeg', 'desc': 'Poste rascador de gran altura (2.15 m) con cápsula transparente y plataformas espirales.', 'isRender': 1},
                'ORION': {'sku': 'PEK-PIS-023', 'dims': 'Alto: 170 cm | Ancho: 70 cm | Fondo: 50 cm', 'img': '/images/products/P28_ORION.jpeg', 'desc': 'Gimnasio robusto de 1.70 m con casita esquinera y múltiples miradores.'},
                'AURORA': {'sku': 'PEK-PIS-024', 'dims': 'Alto: 174 cm | Ancho: 80 cm | Fondo: 75 cm', 'img': '/images/products/P29_AURORA.jpeg', 'desc': 'Gimnasio amplio de gran base con hamacas y niveles para múltiples gatos.'},
                'TOKYO': {'sku': 'PEK-PIS-025', 'dims': 'Alto: 170 cm | Ancho: 70 cm | Fondo: 50 cm', 'img': '/images/products/P30_TOKYO.jpeg', 'desc': 'Torre alta con doble refugio, escalera y postes forrados en cabuya.'},
                'DALTON': {'sku': 'PEK-PIS-026', 'dims': 'Alto: 160 cm | Ancho: 100 cm | Fondo: 45 cm', 'img': '/images/products/P31_DALTON.jpeg', 'desc': 'Gimnasio ancho de 1 metro con hamaca incluida y doble columna de postes.'},
                'URANO': {'sku': 'PEK-PIS-027', 'dims': 'Alto: 150 cm | Ancho: 110 cm | Fondo: 45 cm', 'img': '/images/products/P32_URANO.jpeg', 'desc': 'Gimnasio premium en madera y textil con puente y cápsula acrílica.'},
                'LODUARDO': {'sku': 'PEK-PIS-028', 'dims': 'Alto: 200 cm | Ancho: 90 cm | Fondo: 50 cm', 'img': '/images/products/P33_LODUARDO.jpeg', 'desc': 'Torre monumental de 2 metros con 5 niveles escalonados y casa integrada.'},
                'SATURNO': {'sku': 'PEK-PIS-029', 'dims': 'Alto: 165 cm | Ancho: 110 cm | Fondo: 60 cm', 'img': '/images/products/P34_SATURNO.jpeg', 'desc': 'Gimnasio con domo panorámico transparente, plataformas de madera y postes.'},
                'HEFESTO': {'sku': 'PEK-PIS-030', 'dims': 'Alto: 165 cm | Ancho: 110 cm | Fondo: 70 cm', 'img': '/images/products/P35_HEFESTO.jpeg', 'desc': 'Estructura máxima en triplex de madera con dos burbujas acrílicas, casa y escaleras.'}
            }

            for it in raw_items:
                sec = it['section']
                name = it['name']
                cp = it['catalog_price']
                price = int(round((cp * 1.30) / 1000.0) * 1000) if cp else 0

                if sec == 'Gimnasios de piso':
                    p_id = f'piso-{it["page"]}'
                    f_info = floor_map.get(name, {})
                    sku = f_info.get('sku', f'PEK-PIS-{it["page"]:03d}')
                    dims = f_info.get('dims', it['dimensions'])
                    img = f_info.get('img', f'/images/products/P{it["page"]:02d}_{name}.jpeg')
                    desc = f_info.get('desc', 'Gimnasio de piso para gatos con postes rascadores en cabuya.')
                    is_rend = f_info.get('isRender', 0)
                    includes = json.dumps(['Estructura principal', 'Postes rascadores en cabuya', 'Accesorios de descanso'])
                    tags = json.dumps(['gatos', 'piso', 'etapa-1'])
                    
                    cursor.execute('''
                    INSERT INTO products (id, sku, name, category, stage, catalog_price, price, dimensions, image, description, includes, is_render, is_pending, tags, page)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ''', (p_id, sku, name, sec, 1, cp, price, dims, img, desc, includes, is_rend, 0, tags, it['page']))

                elif sec == 'Gimnasios de pared':
                    c_num = int(name.replace('Combo #', ''))
                    p_id = f'pared-{c_num}'
                    sku = f'PEK-PAR-{c_num:03d}'
                    img = f'/images/products/P{it["page"]:02d}_Combo_{c_num}.jpeg'
                    desc = f'Kit modular completo de pared en triplex de pino de 12 mm para circuito aéreo felino.'
                    dims = 'Composición modular para pared'
                    includes = json.dumps([it['dimensions']] if it['dimensions'] else ['Módulos de pared', 'Rascadores y repisas'])
                    tags = json.dumps(['gatos', 'pared', 'combos', 'etapa-1'])

                    cursor.execute('''
                    INSERT INTO products (id, sku, name, category, stage, catalog_price, price, dimensions, image, description, includes, is_render, is_pending, tags, page)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ''', (p_id, sku, name, sec, 1, cp, price, dims, img, desc, includes, 0, 0, tags, it['page']))

                elif sec == 'Rascadores':
                    p_id = f'rascador-{it["page"]}'
                    sku = f'PEK-RAS-{it["page"]:03d}'
                    img = f'/images/products/P{it["page"]:02d}_{name}.jpeg'
                    desc = 'Rascador para gatos con estructura de soporte y cuerda natural.'
                    dims = it['dimensions']
                    includes = json.dumps(['Rascador principal'])
                    tags = json.dumps(['gatos', 'rascadores', 'etapa-2'])

                    cursor.execute('''
                    INSERT INTO products (id, sku, name, category, stage, catalog_price, price, dimensions, image, description, includes, is_render, is_pending, tags, page)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ''', (p_id, sku, name, sec, 2, cp, price, dims, img, desc, includes, 0, 0, tags, it['page']))

            conn.commit()
            print('Successfully populated database with all products.')

    conn.close()

if __name__ == '__main__':
    init_db()
