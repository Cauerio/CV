<!DOCTYPE html>
<html>

<head>
    <title>rentcar</title>
    <style>
        body {
            font: 100% sans-serif;
        }

        .municipios,
        .filtro_postal,
        .filtro_nombre {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 10px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        .options-container {
            display: flex;
            flex-wrap: wrap;
        }

        .option {
            margin-right: 10px;
        }

        .highlight {
            background-color: yellow;
            font-weight: bold;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>

    <?php
    $url = "https://catalegdades.caib.cat/resource/rjfm-vxun.xml";

    if (!$xml = file_get_contents($url)) {
        echo "Algo no salió como debería";
        die();
    } else {
        $xml = simplexml_load_string($xml);
    }

    $rows = $xml->xpath('//row');
    ?>

    <h2>Filtros:</h2>
    <form method="post" action="">
        <div class="municipios">
            <label for="municipi">Municipio:</label>
            <select name="municipi">
                <option value="" selected>Seleccionar</option>
                <?php
                $municipios = array_unique((array)$xml->xpath("//row/municipi"));
                sort($municipios);
                foreach ($municipios as $municipio) {
                    echo '<option value="' . $municipio . '">' . $municipio . '</option>';
                }
                ?>
            </select>
        </div>

        <div class="filtro_postal">
            <label>Código Postal:</label>
            <div class="options-container">
                <?php
                $CodigosPostales = array();
                foreach ($xml->xpath("//row/adre_a_de_l_establiment") as $postal) {
                    preg_match('/\b\d{5}\b/', $postal, $matches);
                    $codigo_postal = isset($matches[0]) ? $matches[0] : '';
                    if (!empty($codigo_postal)) {
                        $CodigosPostales[] = $codigo_postal;
                    }
                }

                $CodigosPostales = array_unique($CodigosPostales);
                sort($CodigosPostales);
                foreach ($CodigosPostales as $codigo_postal) {
                    echo '<label class="option"><input type="radio" name="adre_a_de_l_establiment" value="' . $codigo_postal . '">' . $codigo_postal . '</label>';
                }
                ?>
            </div>
        </div>

        <div class="filtro_nombre">
            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" />
        </div>

        <br>
        <input type="submit" value="Filtrar Información">
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $seleccion_municipio = isset($_POST['municipi']) ? $_POST['municipi'] : '';
        $seleccion_CodigosPostales = isset($_POST['adre_a_de_l_establiment']) ? $_POST['adre_a_de_l_establiment'] : '';
        $filtro_nombre = isset($_POST['nombre']) ? strtolower($_POST['nombre']) : '';

        if (empty($seleccion_municipio) && empty($seleccion_CodigosPostales) && empty($filtro_nombre)) {
            echo "Por favor, selecciona al menos un municipio, un código postal o ingresa un nombre para filtrar.";
        } else {
            echo "<h2>Información detallada:</h2>";
            echo "<table>
                    <tr>
                        <th>Municipio</th>
                        <th>Dirección</th>
                        <th>Signatura</th>
                        <th>Nombre del comercio</th>
                        <th>Cantidad de vehículos disponibles</th>
                        <th>Nombre de la empresa</th>
                        <th>NIF del explotador</th>
                    </tr>";

            foreach ($rows as $row) {
                $nombreEmpresa = strtolower((string)$row->nom_explotador_s);
                $nombreComercio = strtolower((string)$row->denominaci_comercial);
                $codigo_postal = (string)$row->codigo_postal;

                if (
                    (!empty($seleccion_municipio) && (string)$row->municipi !== $seleccion_municipio) ||
                    (!empty($seleccion_CodigosPostales) && strpos((string)$row->adre_a_de_l_establiment, $seleccion_CodigosPostales) === false) ||
                    (!empty($filtro_nombre) && (
                        strpos($nombreEmpresa, $filtro_nombre) === false ||
                        strpos($nombreComercio, $filtro_nombre) === false
                    ))
                ) {
                    continue;
                }

                echo "<tr>";
                echo "<td>" . (string)$row->municipi . "</td>";
                echo "<td>" . (string)$row->adre_a_de_l_establiment . "</td>";
                echo "<td>" . (string)$row->signatura . "</td>";
                echo "<td>" . highlightFilteredText($nombreComercio, $filtro_nombre) . "</td>";
                echo "<td>" . (string)$row->nombre_de_vehicles . "</td>";
                echo "<td>" . highlightFilteredText($nombreEmpresa, $filtro_nombre) . "</td>";
                echo "<td>" . (string)$row->nif_cif_explotador_s . "</td>";
                echo "</tr>";
            }
                
            echo "</table>";
        }
    }

    function highlightFilteredText($texto, $filtro)
    {
        $destacado = str_replace($filtro, '<span class="highlight">' . $filtro . '</span>', $texto);
        return $destacado;
    }
    ?>

</body>

</html>








