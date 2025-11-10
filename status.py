from flask import Flask, jsonify
from flask_cors import CORS
import socket
import psutil

def get_local_ip():
    hostname = socket.gethostname()
    local_ip = socket.gethostbyname(hostname)
    return local_ip

app = Flask(__name__)
CORS(app)

@app.route('/server-status', methods=['GET'])
def server_status():
    memory = psutil.virtual_memory()
    total_ram_gb = memory.total / (1024 ** 3)
    used_ram_gb = memory.used / (1024 ** 3)
    cpu_percent = psutil.cpu_percent(interval=0.5)

    data = {
        "local_ip": get_local_ip(),
        "ram_percent": memory.percent,
        "ram_used_gb": round(used_ram_gb, 2),
        "ram_total_gb": round(total_ram_gb, 2),
        "cpu_percent": cpu_percent
    }
    return jsonify(data)

if __name__ == '__main__':
    app.run(host='127.0.0.1', port=5000, debug=True)
