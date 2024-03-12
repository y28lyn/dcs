<!DOCTYPE html>
<html x-data="data()" lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../public/css/style.css" rel="stylesheet" />
    <link rel="icon" href="../public/dcs_icon.png" />
    <title>Dashboard DCS</title>
    <style>
        .aside-menu {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            visibility: hidden;
            transition: opacity 0.3s ease-in-out;
            opacity: 0;
        }

        .aside-menu.open {
            visibility: visible;
            opacity: 1;
        }

        .aside-content {
            position: absolute;
            top: 0;
            left: 0;
            width: 80%;
            max-width: 300px;
            height: 100%;
            background-color: white;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);
            transition: transform 0.3s ease-in-out;
            transform: translateX(-100%);
        }

        .aside-content.open {
            transform: translateX(0%);
        }

        .burger-button {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
            cursor: pointer;
        }

        @media screen and (min-width: 768px) {

            .aside-menu,
            .aside-content {
                visibility: visible;
                opacity: 1;
                transform: translateX(0%);
            }

            .burger-button {
                display: none;
            }
        }

        @media screen and (max-width: 767px) {
            .burger-button {
                display: block;
            }
        }
    </style>
</head>

<body class="relative bg-[#E8D8C4] overflow-hidden max-h-screen">
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

    $productVolumes = [];
    $productNames = ['PRODUIT1_1', 'PRODUIT1_4'];
    foreach ($productNames as $productName) {
        $productSql = "
        SELECT lf.mois, SUM(lf.volume) AS MonthlyVolume
        FROM ligne_facturation lf
        INNER JOIN produit p ON p.produitID = lf.produitID
        WHERE p.NOM_PRODUIT = :productName
        AND lf.mois BETWEEN '2021-01-01' AND '2022-04-30'
        GROUP BY lf.mois
        ORDER BY lf.mois
        ";
        $productStmt = $pdo->prepare($productSql);
        $productStmt->bindParam(':productName', $productName);
        $productStmt->execute();
        $monthlyVolumes = $productStmt->fetchAll(PDO::FETCH_ASSOC);
        $productVolumes[$productName] = $monthlyVolumes;
    }

    $jsonProductVolumesData = json_encode($productVolumes);
    ?>

    <div class="burger-button" onclick="toggleAsideMenu()">
        <button class="fixed p-3 -pb-1 z-50 right-6 top-3 rounded-md shadow-lg md:hidden bg-[#301014]">
            <svg class="w-6 h-6 text-[#E8D8C4]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
    </div>

    <aside class="fixed inset-y-0 left-0 bg-white shadow-md max-h-screen w-60 z-10 aside-menu aside-content" id="asideMenu">
        <div class="flex flex-col justify-between h-full">
            <div class="flex-grow">
                <div class="px-4 py-6 text-center border-b">
                    <h1 class="text-xl font-bold leading-none"><span class="text-[#561C24]">Projet</span> DCS</h1>
                </div>
                <div class="p-4">
                    <ul class="space-y-1">
                        <li>
                            <a href="/dcs/src/login.php" class="flex items-center bg-[#561C24] rounded-xl font-bold text-sm text-[#E8D8C4] py-3 px-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="text-lg mr-4" viewBox="0 0 16 16">
                                    <path d="M4 .5a.5.5 0 0 0-1 0V1H2a2 2 0 0 0-2 2v1h16V3a2 2 0 0 0-2-2h-1V.5a.5.5 0 0 0-1 0V1H4V.5zM16 14V5H0v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2zm-3.5-7h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5z" />
                                </svg>Accueil
                            </a>
                        </li>
                        <li>
                            <a href="#firstgraph" class="flex bg-white hover:bg-[#E8D8C4] rounded-xl font-bold text-sm text-gray-900 py-3 px-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="text-lg mr-4" viewBox="0 0 16 16">
                                    <path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zM5 4h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1 0-1zm-.5 2.5A.5.5 0 0 1 5 6h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5zM5 8h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1 0-1zm0 2h3a.5.5 0 0 1 0 1H5a.5.5 0 0 1 0-1z" />
                                </svg>Premier graphique
                            </a>
                        </li>
                        <li>
                            <a href="#secondgraph" class="flex bg-white hover:bg-[#E8D8C4] rounded-xl font-bold text-sm text-gray-900 py-3 px-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="text-lg mr-4" viewBox="0 0 16 16">
                                    <path d="M9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.825a2 2 0 0 1-1.991-1.819l-.637-7a1.99 1.99 0 0 1 .342-1.31L.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3zm-8.322.12C1.72 3.042 1.95 3 2.19 3h5.396l-.707-.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139z" />
                                </svg>Deuxième graphique
                            </a>
                        </li>
                        <li>
                            <a href="#thirdgraph" class="flex bg-white hover:bg-[#E8D8C4] rounded-xl font-bold text-sm text-gray-900 py-3 px-4">
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

    <main class="ml-0 md:ml-60 p-6 max-h-screen overflow-auto">
        <div class="p-6 bg-[#C7B7A3] rounded-lg shadow-lg md:mt-0 mt-12">
            <div id="firstgraph" class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white text-gray-900 rounded-lg shadow-lg p-3">
                <div class="flex flex-col gap-2">
                    <select id="clientSelector" class="block w-full py-3 px-4 border font-semibold bg-[#561C24] text-[#E8D8C4] rounded-xl shadow-lg focus:outline-none focus:ring">
                        <?php
                        $firstClientSelected = true;
                        foreach ($grandClientsList as $client) : ?>
                            <option class="text-[#E8D8C4]" value="<?php echo htmlspecialchars($client); ?>" <?php if ($firstClientSelected) {
                                                                                                                echo "selected";
                                                                                                                $firstClientSelected = false;
                                                                                                            } ?>>
                                <?php echo htmlspecialchars($client); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <img class="hidden md:block" src="https://doodleipsum.com/700x394/flat?i=2439be001f57a11e47a196567e63bc95" alt="">
                </div>
                <?php foreach ($grandClientsList as $client) : ?>
                    <div class="client-chart" id="chart-<?php echo htmlspecialchars($client); ?>" style="display:none;">
                        <h2 class="text-xl text-center font-semibold leading-none mb-4 bg-[#561C24] rounded-xl shadow-md text-[#E8D8C4] py-3 px-4">
                            Top 10 des applications pour <?php echo htmlspecialchars($client); ?>
                        </h2>
                        <div class="pie-chart" id="pie-chart-<?php echo htmlspecialchars($client); ?>"></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div id="secondgraph" class="mt-6">
                <div class="bg-white text-gray-900 rounded-lg shadow-lg mt-3 p-3">
                    <h2 class="text-xl text-center font-semibold leading-none mb-4 bg-[#561C24] rounded-xl shadow-md text-[#E8D8C4] py-3 px-4">
                        Évolution des montants pour les 5 premiers clients de janvier 2021 à avril 2022
                    </h2>
                    <div id="line-chart"></div>
                </div>
            </div>
            <div id="thirdgraph" class="mt-6">
                <div class="bg-white text-gray-900 rounded-lg shadow-lg mt-3 p-3">
                    <h2 class="text-xl text-center font-semibold leading-none mb-4 bg-[#561C24] rounded-xl shadow-md text-[#E8D8C4] py-3 px-4">
                        Évolution des volumes des produits 1_1 et 1_4
                    </h2>
                    <div id="volume-chart" class="chart-container"></div>
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

            window.addEventListener('resize', function() {
                let allCharts = ApexCharts.instances;
                allCharts.forEach(function(chart) {
                    chart.updateOptions({
                        dataLabels: {
                            enabled: window.innerWidth >= 768
                        }
                    });
                });
            });

            function createPieChart(chartData, elementId) {
                let options = {
                    series: chartData.map(a => parseFloat(a.ChiffreAffaires)),
                    chart: {
                        type: 'pie',
                        height: 400
                    },
                    labels: chartData.map(a => a.Application),
                    dataLabels: {
                        enabled: window.innerWidth >= 768
                    },
                    legend: {
                        show: true,
                        formatter: function(val, opts) {
                            return (opts.seriesIndex + 1) + '. ' + val;
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(value) {
                                return new Intl.NumberFormat('fr-FR', {
                                    style: 'currency',
                                    currency: 'EUR'
                                }).format(value);
                            }
                        }
                    },
                    responsive: [{
                        breakpoint: 768,
                        options: {
                            dataLabels: {
                                enabled: false
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }]
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
                    type: 'datetime',
                    labels: {
                        formatter: function(value, timestamp) {
                            var date = new Date(timestamp);
                            var monthNames = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
                            return date.getFullYear().toString().substr(-2) + ' ' + monthNames[date.getMonth()];
                        }
                    }
                }
            };

            var chart = new ApexCharts(document.querySelector("#line-chart"), options);
            chart.render();
        });
    </script>

    <!-- Troisième graphique -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var productVolumesData = <?php echo $jsonProductVolumesData; ?>;
            var productSeries = [];

            for (var productId in productVolumesData) {
                var productDataSeries = {
                    name: 'Produit ' + productId,
                    data: productVolumesData[productId].map(function(item) {
                        return [new Date(item.mois), parseFloat(item.MonthlyVolume)];
                    })
                };
                productSeries.push(productDataSeries);
            }

            var productOptions = {
                series: productSeries,
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
                            return value.toLocaleString('fr-FR');
                        }
                    }
                },
                xaxis: {
                    type: 'datetime',
                    labels: {
                        formatter: function(value, timestamp) {
                            var date = new Date(timestamp);
                            var monthNames = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
                            return date.getFullYear().toString().substr(-2) + ' ' + monthNames[date.getMonth()];
                        }
                    }
                }
            };

            var productChart = new ApexCharts(document.querySelector("#thirdgraph .chart-container"), productOptions);
            productChart.render();
        });
    </script>

    <script>
        function toggleAsideMenu() {
            var asideMenu = document.getElementById('asideMenu');
            asideMenu.classList.toggle('open');
        }

        document.addEventListener('DOMContentLoaded', function() {
            var asideLinks = document.querySelectorAll('.aside-menu a');
            asideLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    var asideMenu = document.getElementById('asideMenu');
                    asideMenu.classList.remove('open');
                });
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</body>

</html>