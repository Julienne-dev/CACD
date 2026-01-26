<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        body{
            background: #b3d7e6ff;
        }

               form {
            margin: auto;
            max-width: 600px;
            background-color: #636d6b1c;
            border-radius: 30px;
        }

                h2{
            text-align: center; 
        }

        .form-group {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
        }

        label {
            width: 160px;
            text-align: right;
            margin-right: 10px;
        }

        input, select, textarea {
            flex: 1;
            padding: 8px;
            
        }

        button {
            margin-left: 250px;
            margin-bottom: 20px ;
            border-radius: 8px ;
            width: 120px;
            height: 35px;
            background: #38a7d6ff;
            border: none;
            color: white;
        }

        div{
            margin: 20px;
        }

        input, select{
            margin-right: none;
            width: 200;
        }

        #div{
            display: flex; 
        }
    </style>
</head>
<body>
    <h2>Bienvenue sur le formulaire de commande en ligne</h2>
    <form action="ajout_commande.php" method="post">
        <div id="div">
            <div>
                <div>
                    <label>Nom :</label><br>
                    <input type="text" name="nom" required/>
                </div>

                <div>
                    <label>Prénom :</label><br>
                    <input type="text" name="prenom" required/>
                </div>

                <div>
                    <label>Sexe :</label><br>
                    <select name="sexe">
                        <option disabled >--Choisissez--</option>
                        <option>Féminin</option>
                        <option>Masculin</option>
                        <option>Autre</option>
                    </select>
                </div>

                <div>
                    <label>Email :</label><br>
                    <input type="email" name="email" required/> 
                </div>
                <div>
                    <label>Téléphone : </label><br>
                    <input type="text" name="tel"/>
                </div>
            </div>

            <div>
                <div>
                    <label>Age: </label><br>
                    <input type="number" name="age" required/>
                </div>

                <div>
                    <label>Marque de téléphone : </label><br>
                    <input type="text" name="marque" />
                </div>

                <div>
                    <label>Date de livraison : </label><br>
                    <input type="date" name="date_livraison" required/>
                </div>

                <div>
                    <label>Adresse : </label><br>
                    <input type="text" name="adresse" required/>
                </div>

                <div>
                    <label>Commentaire : </label><br>
                    <textarea name="commentaire"></textarea>
                </div>
            </div>
        </div>
            <button type="submit" name="valider">Valider</button>
    </form>
</body>
</html>

