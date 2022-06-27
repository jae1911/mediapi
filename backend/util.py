from redis import Redis
from werkzeug.security import generate_password_hash, check_password_hash
from pickle import dumps, loads
from logging import getLogger
from os import environ
from flask import request, jsonify
from jwt import decode, encode
from functools import wraps
from uuid import uuid4
from datetime import datetime, timedelta
from re import findall, match

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


# Env secret key
secret_key = str(environ.get("secret_key"))

# Register user
def create_user(username, password):
    """Creates the user in the DB"""
    user = Users.query.filter_by(name=username).first()
    if user:
        return None

    hashed_password = generate_password_hash(password)
    public_id = str(uuid4())

    try:
        new_user = Users(
            public_id=public_id, name=username, password=hashed_password, admin=False
        )
        db.session.add(new_user)
        db.session.commit()
    except:
        return None

    return public_id


# Generate token
def verify_login(username, password):
    """Verifies a login request and returns a token if ok"""
    user = Users.query.filter_by(name=username).first()

    if check_password_hash(user.password, password):
        token = encode(
            {"public_id": user.public_id, "exp": datetime.utcnow() + timedelta(days=7)},
            secret_key,
            "HS256",
        )
        return token

    return None


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
            data = decode(token, secret_key, algorithms=["HS256"])
            current_user = Users.query.filter_by(public_id=data["public_id"]).first()
        except:
            return jsonify({"err": "the provided token is invalid"})

        return f(current_user, *args, **kwargs)

    return decorator


# ISBN validation
def check(isbn):
    d = findall(r"\d", isbn)
    if len(d) != 13:
        return False
    if not match(r"97[89](?:-\d+){3}-\d$", isbn):
        return False

    # The ISBN-13 check digit, which is the last digit of the ISBN, must range from 0 to 9
    # and must be such that the sum of all the thirteen digits, each multiplied by its
    # (integer) weight, alternating between 1 and 3, is a multiple of 10.
    odd = [int(x) for x in d[::2]]
    even = [int(x) * 3 for x in d[1::2]]
    return (sum(odd) + sum(even)) % 10 == 0
