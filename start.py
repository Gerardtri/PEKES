#!/usr/bin/env python3
"""
PEKES — Lanzador Dual (Frontend + Backend Python)
Inicia el servidor Python Flask en el puerto 5001 y el servidor Vite en el puerto 5173.
"""
import subprocess
import sys
import time
import os
import webbrowser

ROOT_DIR = os.path.dirname(os.path.abspath(__file__))

def main():
    print("=" * 60)
    print("🐾 INICIANDO ECOMMERCE PEKES (PYTHON + FRONTEND) 🐾")
    print("=" * 60)

    # 1. Start Python Backend
    print("-> Iniciando Backend Python (Flask + SQLite)...")
    backend_proc = subprocess.Popen(
        [sys.executable, os.path.join(ROOT_DIR, "backend", "app.py")],
        cwd=ROOT_DIR
    )

    # 2. Start Frontend Vite
    print("-> Iniciando Frontend Web (Vite)...")
    frontend_proc = subprocess.Popen(
        ["npm", "run", "dev"],
        cwd=ROOT_DIR
    )

    time.sleep(2)
    print("\n" + "=" * 60)
    print("✨ ¡TODO LISTO Y FUNCIONANDO! ✨")
    print("  🌐 Tienda Pública:      http://localhost:5173/")
    print("  📡 API REST de Productos: http://localhost:5001/api/products")
    print("=" * 60)
    print("Presiona CTRL + C para detener ambos servidores.\n")

    try:
        backend_proc.wait()
        frontend_proc.wait()
    except KeyboardInterrupt:
        print("\nDeteniendo servidores...")
        backend_proc.terminate()
        frontend_proc.terminate()
        print("Servidores detenidos con éxito.")

if __name__ == '__main__':
    main()
