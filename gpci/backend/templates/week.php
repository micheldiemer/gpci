<html>
<style type="text/css">
    table.agenda {
        font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
        border-collapse: collapse;
        width: 100%;
    }

    table.agenda td,
    table.agenda th {
        border: 1px solid #fff;
        padding: 8px;
        text-align: center;
        width: 16%;
    }

    table.agenda td {
        padding-top: 12px;
        padding-bottom: 12px;
        background-color: #bcecff;
        color: black;
        word-break: break-word;
    }

    table.agenda th {
        background: #004b9f;
        color: white;
    }

    table.agenda th:nth-child(0) {
        background: #00739f;
        color: white;
    }

    .header {}

    .header-title {
        font-weight: bold;
        text-align: center;
        margin-top: -70px;
        margin-bottom: 50px;
        font-size: 36px;
    }

    .logo {
        width: 128px;
    }
</style>
<div class="header">
    <img src="<?= IFIDE_LOGO_URL ?>" alt="Logo IFIDE SUP'FORMATION" class="logo">
    <h1 class=" header-title">PLANNING <?php echo $classe->nom . " - Semaine " . $week; ?></h1>
</div>
<table class="agenda">

    <thead>
        <tr>
            <th>Semaine <?php echo ($week) ?></th>
            <?php
            $count = 0;
            foreach ($date as $day) {
                $date = DateTimeImmutable::createFromFormat('U', strtotime($day));
                echo ("<th>" . $date_name[$count] . "<br>" . $date->format('d/m') . "</th>");
                $count += 1;
            }
            ?>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th>
                8h00<br>
                -<br>
                12h00
            </th>
            <?php
            foreach ($cours_am as $cours) {
                if ($cours) {
                    echo ("<td><strong>" . $cours->matiere->nom . "</strong><br>");
                    if ($cours->user && ($cours->user->lastName || $cours->user->firstName)) {
                        echo (str_replace("é", "É", str_replace("è", "È", strtoupper($cours->user->lastName) . " " . $cours->user->firstName[0])));
                    } else {
                        echo ('Enseignant non spécifié.');
                    }
                    echo (".<br>" . $cours->salle->nom . "</td>");
                } else {
                    echo ("<td></td>");
                }
            }
            ?>
        </tr>
        <tr>
            <th>
                13h00<br>
                -<br>
                17h00
            </th>
            <?php
            foreach ($cours_pm as $cours) {
                if ($cours) {
                    echo ("<td><strong>" . $cours->matiere->nom . "</strong><br>");
                    if ($cours->user && ($cours->user->lastName || $cours->user->firstName)) {
                        echo (str_replace("é", "É", str_replace("è", "È", strtoupper($cours->user->lastName) . " " . $cours->user->firstName[0])));
                    } else {
                        echo ('Enseignant non spécifié.');
                    }
                    echo (".<br>" . $cours->salle->nom . "</td>");
                } else {
                    echo ("<td></td>");
                }
            }
            ?>
        </tr>
    </tbody>
</table>

</html>