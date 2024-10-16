<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/dashboard-lms/public/assets/img/head-sena.svg">
    <title>Error-Centro</title>
</head>
<style>
    @import url('https://fonts.googleapis.com/css?family=Nunito+Sans');

    :root {
        --blue: #0e0620;
        --white: #fff;
        --green: #39a900;
    }

    html,
    body {
        height: 100%;
    }

    body {
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: "Nunito Sans";
        color: var(--blue);
        font-size: 1em;
    }

    button {
        font-family: "Nunito Sans";
    }

    h1 {
        font-size: 7.5em;
        margin: 15px 0px;
        font-weight: bold;
    }

    h2 {
        font-weight: bold;
    }

    .btn {
        z-index: 1;
        overflow: hidden;
        background: transparent;
        position: relative;
        padding: 8px 50px;
        border-radius: 30px;
        cursor: pointer;
        font-size: 1em;
        letter-spacing: 2px;
        transition: 0.2s ease;
        font-weight: bold;
        margin: 5px 0px;

        &.green {
            border: 4px solid var(--green);
            color: var(--blue);

            &:before {
                content: "";
                position: absolute;
                left: 0;
                top: 0;
                width: 0%;
                height: 100%;
                background: var(--green);
                z-index: -1;
                transition: 0.2s ease;
            }

            &:hover {
                color: var(--white);
                background: var(--green);
                transition: 0.2s ease;

                &:before {
                    width: 100%;
                }
            }
        }
    }

    @media screen and (max-width:768px) {
        body {
            display: block;
        }

        .container {
            margin-top: 70px;
            margin-bottom: 70px;
        }
    }
</style>

<body>
    <main>
        <div class="container">
            <div class="row">
                <div class="col-md-6 align-self-center">
                    <h2>Parece que algo ha salido mal.</h2>
                    <h2>Por favor ingresa nuevamente.</h2>
                    <button class="btn green" onclick="redirect()">Inicio</button>
                </div>
            </div>
        </div>
    </main>

    <script>
        function redirect() {
            window.location.href = '/zajuna/my/';
        }
    </script>
</body>

</html>