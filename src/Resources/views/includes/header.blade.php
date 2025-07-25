<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <style>
        :root {
            --brand-light: #061523;
            --brand-dark: #030B12;
            --brand-primary: #1A315C;
            --brand-secondary: #176097;
        }

        body {
            background: var(--brand-dark) !important;
            height: 100vh !important;
            width: 100% !important;
            font-family: "Sans Serif Collection", sans-serif;
        }

        /**
            Navbar
         */
        nav {
            height: 75px;
            max-height: 90px;
            width: 100%;
            background: var(--brand-light);
            padding: 0 8%;
        }

        nav div a {
            text-decoration: none;
            color: var(--bs-white);
            font-size: 18px;
        }

        nav div a:hover {
            border-bottom: 5px solid var(--brand-secondary);
        }

        .active-item {
            border-bottom: 5px solid var(--brand-secondary) !important;
        }


        /**
           overriding bootstrap classes
         */
        .card {
            background-color: var(--brand-light) !important;
            border-radius: 23px !important;
            padding: 10px !important;
        }

        /*
           main pages
         */
        div .card {
            width: 75%;
        }


        /** forms */
        select {
            background-color: transparent !important;
            color: var(--bs-white);
            border-color: var(--brand-secondary);
            outline-color: var(--brand-secondary) !important;
            padding: 5px;
            outline: none;
            cursor: pointer;
        }

        select:focus {
            outline-color: var(--brand-secondary) !important;
            border-color: var(--brand-secondary);
        }

        select::content {
            color: var(--brand-secondary);
        }

        select option {
            color: var(--bs-black);
        }

        .submit-button {
            width: 30% !important;
            background-color: var(--brand-primary) !important;
            border-radius: 5px !important;
            border: 0;
            outline: 0 !important;
            color: var(--bs-white) !important;
        }

        .brand-input {
            background-color: transparent !important;
            color: var(--bs-white) !important;
            border-color: var(--brand-secondary) !important;
            box-shadow: none !important;
            border-radius: 5px;
            border: 1px solid;
            outline: 0;
            padding: 1px 15px;
        }

        .brand-input::placeholder {
            color: var(--bs-white) !important;
        }

        .brand-input:focus {
            border-color: var(--brand-secondary) !important;
            outline-color: var(--brand-secondary) !important;
        }

        input[type="checkbox"] {
            cursor: pointer !important;
        }


        /** Logs */
        #terminal-wrapper {
            position: fixed;
            width: 100%;
            bottom: 0;
            left: 0;
            border: 1px solid var(--brand-light);
            border-radius: 4px;
            background-color: var(--brand-dark);
        }

        #terminal {
            background-color: var(--brand-dark);
            color: #d4d4d4;
            height: 50px;
            overflow-y: auto;
            padding: 10px;
            font-family: 'Courier New', Courier, monospace;
            max-height: 75vh;
        }

        .terminal-header {
            background-color: var(--brand-light);
            color: var(--brand-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px;
        }

        .resizer {
            width: 100%;
            height: 5px;
            cursor: ns-resize;
            background-color: transparent;
        }


        /**
            animations
         */

        .scale-up-ver-bottom {
            -webkit-animation: scale-up-ver-bottom 0.4s cubic-bezier(0.390, 0.575, 0.565, 1.000) both;
            animation: scale-up-ver-bottom 0.4s cubic-bezier(0.390, 0.575, 0.565, 1.000) both;
        }

        @-webkit-keyframes scale-up-ver-bottom {
            0% {
                -webkit-transform: scaleY(0.4);
                transform: scaleY(0.4);
                -webkit-transform-origin: 0 100%;
                transform-origin: 0 100%;
            }
            100% {
                -webkit-transform: scaleY(1);
                transform: scaleY(1);
                -webkit-transform-origin: 0% 100%;
                transform-origin: 0% 100%;
            }
        }

        @keyframes scale-up-ver-bottom {
            0% {
                -webkit-transform: scaleY(0.4);
                transform: scaleY(0.4);
                -webkit-transform-origin: 0% 100%;
                transform-origin: 0% 100%;
            }
            100% {
                -webkit-transform: scaleY(1);
                transform: scaleY(1);
                -webkit-transform-origin: 0% 100%;
                transform-origin: 0% 100%;
            }
        }


        /**
            utilities
         */

        .hidden {
            display: none !important;
        }


        /** Loading Modal */


        .lds-dual-ring {
            display: inline-block;
            width: 80px;
            height: 80px;
            text-align: center;
        }

        .lds-dual-ring:after {
            content: " ";
            display: block;
            width: 64px;
            height: 64px;
            margin: 8px;
            border-radius: 50%;
            border: 6px solid var(--bs-white);
            border-color: var(--bs-white) transparent var(--bs-white) transparent;
            animation: lds-dual-ring 1.2s linear infinite;
        }

        @keyframes lds-dual-ring {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        .modal-content {
            color: var(--bs-white) !important;
            border: none; /* Remove the default border */
            box-shadow: none; /* Remove the default box shadow */
            border-radius: 15px; /* Remove the default border radius */
        }

        .modal-title {
            color: var(--bs-white) !important;
        }

        .modal-header {
            color: var(--bs-white) !important;
            border-bottom: none; /* Remove the border from the header */
        }

        .modal-footer {
            color: var(--bs-white) !important;
            border-top: none; /* Remove the border from the footer */
        }

        .modal-body {
            color: var(--bs-white) !important;
            background-color: var(--brand-light) !important;
        }


        /*scrollbar*/

        ::-webkit-scrollbar {
            width: 2px;
        }

        ::-webkit-scrollbar-thumb {
            background-color: var(--brand-light);
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background-color: var(--brand-light);
        }

        pre code  {
          background-color: var(--brand-light);
          border: 1px solid var(--brand-dark);
          border-radius: 15px;
          display: block;
          padding: 20px;
          width: 100%;
          color: white !important;
          overflow: scroll;
        }

    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <title>Cubeta-Starter</title>
</head>
