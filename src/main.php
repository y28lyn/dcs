<!DOCTYPE html>
<html x-data="data()" lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="../public/css/style.css" rel="stylesheet" />
    <link rel="icon" href="../public/dcs_icon.png" />
    <title>Dashboard DCS</title>
</head>

<body class="relative bg-yellow-50 overflow-hidden max-h-screen">
    <?php
    include '../includes/connexion.php';

    $grandClientsList = ['Client1', 'Client2', 'Client3'];
    $allResults = [];

    foreach ($grandClientsList as $client) {
        try {
            $sql = "SELECT gc.NomGrandClient, a.nomAppli AS Application, SUM(lf.prix) AS ChiffreAffaires
                FROM grandclients gc
                JOIN clients c ON gc.GrandClientID = c.GrandClientID
                JOIN centresactivite ca ON c.CentreActiviteID = ca.CentreActiviteID
                JOIN ligne_facturation lf ON ca.CentreActiviteID = lf.CentreActiviteID
                JOIN application a ON lf.IRT = a.IRT
                WHERE gc.NomGrandClient = :clientName
                GROUP BY gc.NomGrandClient, a.nomAppli
                ORDER BY ChiffreAffaires DESC
                LIMIT 10;";
            $requete = $pdo->prepare($sql);
            $requete->bindParam(':clientName', $client, PDO::PARAM_STR);
            $requete->execute();
            $allResults[$client] = $requete->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo 'Erreur : ' . $e->getMessage();
        }
    }

    $topClientsSql = "
    SELECT gc.NomGrandClient, SUM(lf.volume) AS TotalVolume
    FROM grandclients gc
    JOIN clients c ON gc.GrandClientID = c.GrandClientID
    JOIN ligne_facturation lf ON c.CentreActiviteID = lf.CentreActiviteID
    WHERE lf.mois BETWEEN '2021-01-01' AND '2022-04-30'
    GROUP BY gc.NomGrandClient
    ORDER BY TotalVolume DESC
    LIMIT 5
    ";
    $topClientsStmt = $pdo->prepare($topClientsSql);
    $topClientsStmt->execute();
    $topClients = $topClientsStmt->fetchAll(PDO::FETCH_ASSOC);

    $clientMonthlyAmounts = [];
    foreach ($topClients as $client) {
        $clientSql = "
        SELECT lf.mois, SUM(lf.prix) AS MonthlyAmount
        FROM ligne_facturation lf
        JOIN clients c ON c.CentreActiviteID = lf.CentreActiviteID
        JOIN grandclients gc ON c.GrandClientID = gc.GrandClientID
        WHERE gc.NomGrandClient = :NomGrandClient
        AND lf.mois BETWEEN '2021-01-01' AND '2022-04-30'
        GROUP BY lf.mois
        ORDER BY lf.mois
        ";
        $clientStmt = $pdo->prepare($clientSql);
        $clientStmt->bindValue(':NomGrandClient', $client['NomGrandClient']);
        $clientStmt->execute();
        $monthlyAmounts = $clientStmt->fetchAll(PDO::FETCH_ASSOC);
        $clientMonthlyAmounts[$client['NomGrandClient']] = $monthlyAmounts;
    }

    $jsonMonthlySalesData = json_encode($clientMonthlyAmounts);
    ?>



    <aside class="fixed inset-y-0 left-0 bg-white shadow-md max-h-screen w-60">
        <div class="flex flex-col justify-between h-full">
            <div class="flex-grow">
                <div class="px-4 py-6 text-center border-b">
                    <h1 class="text-xl font-bold leading-none"><span class="text-yellow-700">Projet</span> DCS</h1>
                </div>
                <div class="p-4">
                    <ul class="space-y-1">
                        <li>
                            <a href="/dcs/src/main.php" class="flex items-center bg-yellow-200 rounded-xl font-bold text-sm text-yellow-900 py-3 px-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="text-lg mr-4" viewBox="0 0 16 16">
                                    <path d="M4 .5a.5.5 0 0 0-1 0V1H2a2 2 0 0 0-2 2v1h16V3a2 2 0 0 0-2-2h-1V.5a.5.5 0 0 0-1 0V1H4V.5zM16 14V5H0v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2zm-3.5-7h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5z" />
                                </svg>Accueil
                            </a>
                        </li>
                        <li>
                            <a href="#firstgraph" class="flex bg-white hover:bg-yellow-50 rounded-xl font-bold text-sm text-gray-900 py-3 px-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="text-lg mr-4" viewBox="0 0 16 16">
                                    <path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zM5 4h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1 0-1zm-.5 2.5A.5.5 0 0 1 5 6h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5zM5 8h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1 0-1zm0 2h3a.5.5 0 0 1 0 1H5a.5.5 0 0 1 0-1z" />
                                </svg>Premier graphique
                            </a>
                        </li>
                        <li>
                            <a href="#secondgraph" class="flex bg-white hover:bg-yellow-50 rounded-xl font-bold text-sm text-gray-900 py-3 px-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="text-lg mr-4" viewBox="0 0 16 16">
                                    <path d="M9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.825a2 2 0 0 1-1.991-1.819l-.637-7a1.99 1.99 0 0 1 .342-1.31L.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3zm-8.322.12C1.72 3.042 1.95 3 2.19 3h5.396l-.707-.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139z" />
                                </svg>Deuxième graphique
                            </a>
                        </li>
                        <li>
                            <a href="#thirdgraph" class="flex bg-white hover:bg-yellow-50 rounded-xl font-bold text-sm text-gray-900 py-3 px-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="text-lg mr-4" viewBox="0 0 16 16">
                                    <path d="M2 1a1 1 0 0 0-1 1v4.586a1 1 0 0 0 .293.707l7 7a1 1 0 0 0 1.414 0l4.586-4.586a1 1 0 0 0 0-1.414l-7-7A1 1 0 0 0 6.586 1H2zm4 3.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z" />
                                </svg>Troisième graphique
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </aside>

    <header class="z-50 fixed right-0 top-0 left-60 bg-yellow-50 px-4 py-5 border-b">
        <div class="max-w-4xl mx-auto">
            <div class="flex items-center justify-center">
                <div class="text-2xl font-bold bg-yellow-200 rounded-xl text-yellow-900 py-3 px-4">Lycée Jacques Brel</div>
            </div>
        </div>
    </header>

    <main class="ml-60 py-16 px-6 max-h-screen overflow-auto">
        <div class="mt-12 p-6 bg-amber-200 rounded-lg shadow-lg">
            <div id="firstgraph" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <select id="clientSelector" class="block w-full px-4 py-2 border font-semibold text-yellow-900 rounded-lg shadow-lg focus:outline-none focus:ring">
                        <?php
                        $firstClientSelected = true;
                        foreach ($grandClientsList as $client) : ?>
                            <option class="text-yellow-900" value="<?php echo htmlspecialchars($client); ?>" <?php if ($firstClientSelected) {
                                                                                                                    echo "selected";
                                                                                                                    $firstClientSelected = false;
                                                                                                                } ?>>
                                <?php echo htmlspecialchars($client); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php foreach ($grandClientsList as $client) : ?>
                    <div class="bg-white text-gray-900 rounded-lg shadow-lg p-3 client-chart" id="chart-<?php echo htmlspecialchars($client); ?>" style="display:none;">
                        <h2 class="text-xl text-center font-semibold leading-none mb-4 bg-yellow-200 rounded-xl shadow-md text-yellow-900 py-3 px-4">
                            Top 10 des applications pour <?php echo htmlspecialchars($client); ?>
                        </h2>
                        <div class="pie-chart" id="pie-chart-<?php echo htmlspecialchars($client); ?>"></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div id="secondgraph" class="mt-6">
                <div class="bg-white text-gray-900 rounded-lg shadow-lg mt-3 p-3">
                    <h2 class="text-xl text-center font-semibold leading-none mb-4 bg-yellow-200 rounded-xl shadow-md text-yellow-900 py-3 px-4">
                        Évolution des montants pour les 5 premiers clients de janvier 2021 à avril 2022
                    </h2>
                    <div id="line-chart"></div>
                </div>
            </div>
        </div>
    </main>

    <!-- Premier graphique -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var allResults = <?php echo json_encode($allResults); ?>;
            var clientSelector = document.getElementById('clientSelector');
            var charts = document.querySelectorAll('.client-chart');

            function createPieChart(chartData, elementId) {
                let options = {
                    series: chartData.map(a => parseFloat(a.ChiffreAffaires)),
                    chart: {
                        type: 'donut',
                        height: 400
                    },
                    labels: chartData.map(a => a.Application),
                    legend: {
                        position: 'right'
                    },
                };

                let chartElement = document.getElementById(elementId);
                let chart = new ApexCharts(chartElement, options);
                chart.render();
            }

            clientSelector.addEventListener('change', function() {
                var selectedClient = this.value;

                charts.forEach(function(chart) {
                    chart.style.display = 'none';
                });

                var selectedChart = document.getElementById('chart-' + selectedClient);
                if (selectedChart) {
                    selectedChart.style.display = 'block';
                }
            });

            charts.forEach(function(chart) {
                chart.style.display = 'none';
            });

            for (let client in allResults) {
                createPieChart(allResults[client], `pie-chart-${client}`);
            }

            updateDisplayedChart(clientSelector.value);

            function updateDisplayedChart(clientName) {
                charts.forEach(function(chart) {
                    chart.style.display = 'none';
                });

                var selectedChart = document.getElementById('chart-' + clientName);
                if (selectedChart) {
                    selectedChart.style.display = 'block';
                }
            }
        });
    </script>

    <!-- Deuxième graphique -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var monthlySalesData = <?php echo $jsonMonthlySalesData; ?>;
            var series = [];

            for (var client in monthlySalesData) {
                var dataSeries = {
                    name: client,
                    data: monthlySalesData[client].map(function(item) {
                        return [new Date(item.mois), parseFloat(item.MonthlyAmount)];
                    })
                };
                series.push(dataSeries);
            }

            var options = {
                series: series,
                chart: {
                    height: 400,
                    type: 'line',
                    zoom: {
                        enabled: false
                    },
                    locales: [{
                        name: 'fr',
                        options: {
                            months: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
                            shortMonths: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
                            days: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
                            shortDays: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
                            toolbar: {
                                exportToSVG: "Télécharger SVG",
                                exportToPNG: "Télécharger PNG",
                                exportToCSV: "Télécharger CSV",
                                menu: "Menu",
                            }
                        }
                    }],
                    defaultLocale: 'fr'
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'straight'
                },
                grid: {
                    row: {
                        colors: ['#f3f3f3', 'transparent'],
                        opacity: 0.5
                    },
                },
                yaxis: {
                    labels: {
                        formatter: function(value) {
                            return value.toLocaleString('fr-FR') + " €";
                        }
                    }
                },
                xaxis: {
                    type: 'datetime'
                }
            };

            var chart = new ApexCharts(document.querySelector("#line-chart"), options);
            chart.render();
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</body>

</html>