from redis import Redis
from werkzeug.security import generate_password_hash, check_password_hash
from pickle import dumps, loads
from logging import getLogger
from os import environ
from flask import request, jsonify
from jwt import decode
from functools import wraps

from app import db

# Setup logger
log = getLogger("mediapi.backend.util")

# Setup Redis
redis_host = environ.get("REDIS_ENDPOINT", "localhost")
redis_port = int(environ.get("REDIS_PORT", "6379"))
redis_db = int(environ.get("REDIS_DB", "0"))

r = Redis(host=redis_host, port=redis_port, db=redis_db)

# Data storing
def cache_val(key, val, expiration_secs=60):
    """Stores the value in the Redis"""
    if not key or not val:
        return None

    try:
        s = dumps(val)
        r.set(key, s, expiration_secs or None)
        log.info(f"Stored {key}")
    except:
        log.exception(f"cache_val {key}")


def get_val(key, default=None):
    """Returns key value from Redis"""
    if not key:
        return default

    try:
        v = r.get(key)
        if not v:
            return default

        return loads(v) or default
    except:
        log.exception(f"get_val {key}")
        return default


# DB stuff


class Users(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    public_id = db.Column(db.Integer)
    name = db.Column(db.String(50))
    password = db.Column(db.String(50))
    admin = db.Column(db.Boolean)


def init_db():
    """Initialize the DB"""
    db.create_all()


# Request verification


def token_required(f):
    """For requests asking for a token"""

    @wraps(f)
    def decorator(*args, **kwargs):
        token = None
        if "x-access-tokens" in request.headers:
            token = request.headers["x-access-tokens"]

        if not token:
            return jsonify({"err": "token is missing"})

        try:
            data = jwt.decode(token, app.config["SECRET_KEY"], algorithms=["HS256"])
            current_user = Users.query.filter_by(public_id=data["public_id"]).first()
        except:
            return jsonify({"err": "the provided token is invalid"})

        return f(current_user, *args, **kwargs)

    return decorator
