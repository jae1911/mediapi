from os import environ
from logging import getLogger

from flask import Flask, request, jsonify
from flask_sqlalchemy import SQLAlchemy
from requests import get

# Setup logger
log = getLogger("mediapi.backend.app")

# Environment
secret_key = environ.get("secret_key")
omdb_endpoint = environ.get("OMDB_ENDPOINT", "http://www.omdbapi.com/")
omdb_key = environ.get("OMDB_KEY")

# Setup app and important stuff
app = Flask(__name__)
app.config["SQLALCHEMY_DATABASE_URI"] = "sqlite:///db/backend.db"
app.config["SQLALCHEMY_TRACK_MODIFICATIONS"] = True
app.config["SECRET_KEY"] = secret_key
db = SQLAlchemy(app)

from util import cache_val, get_val, init_db, token_required, verify_login, create_user

init_db()

if __name__ == "__app__":
    app.run()


@app.route("/")
def hello_world():
    return "You're not supposed to be here", 200


@app.post("/api/register")
def api_register():
    data = request.get_json()

    if not data or not "username" in data or not "password" in data:
        return jsonify({"err": "could not register user"})

    res = create_user(data["username"], data["password"])

    if not res:
        return jsonify({"err": "registration failed (db error?)"})

    return jsonify({"ok": res})


@app.post("/api/login")
def api_login():
    data = request.get_json()

    if not data or not data["username"] or not data["password"]:
        return jsonify({"err": "could not login user"})

    res = verify_login(data["username"], data["password"])

    if not res:
        return jsonify({"err": "invalid login or password"})

    return jsonify({"ok": res})


@app.post("/getMovie")
@token_required
def get_movie():
    req = request.get_json()

    if not req:
        return jsonify({"err": "no request"})

    if not "title" in req:
        return jsonify({"err": "missing important parameter"})

    # Get content from request
    title = req["title"]
    year = None
    plot_version = None
    if "year" in req:
        year = req["year"]
    if "plot_version" in req:
        plot_version = req["plot_version"]

    cache_name = f"movie_search_cached_{title}"
    if year:
        cache_name = f"{cache_name}_{year}"
    if plot_version:
        cache_name = f"{cache_name}_{plot_version}"

    cached = get_val(cache_name)
    if cached:
        return jsonify(cached), 200

    # Only search if no cache hit
    request_uri = f"{omdb_endpoint}/?apikey={omdb_key}&t={title}"

    # Not the best way of doing this but hey, it works
    if year:
        request_uri = f"{request_uri}&y={year}"
    if plot_version:
        request_uri = f"{request_uri}&y={plot_version}"

    response = get(request_uri)

    # In case of exception
    if response.status_code != 200:
        log.exception(f"get_movie: response code isn't 200: {response.content}")

    # Try and parse the content otherwise return error
    try:
        response_json = response.json()

        cache_val(cache_name, response_json, 3600)
        return jsonify(response_json)
    except:
        cache_val(cache_name, response.content, 3600)
        return jsonify(response.content)
