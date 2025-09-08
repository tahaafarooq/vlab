from flask import Flask, request, render_template, render_template_string, jsonify

app = Flask(__name__)


@app.route('/', methods=['GET', 'POST'])
def index():
    if request.method == "POST":
        print(request.form.values)
        username = request.form["username"]

        if username is not None:
            return render_template_string(f"Hola {username}")
        else:
            return render_template("index.html")
    else:
        return render_template("index.html")


if __name__ == "__main__":
    app.run(host='0.0.0.0', port=8888, debug=True)
