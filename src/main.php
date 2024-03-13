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

    $revenueSql = "
        SELECT
        fam.FAMILLE_NAME,
        DATE_FORMAT(lf.mois, '%Y-%m') AS Month,
        SUM(lf.prix * lf.volume) AS TotalRevenue
        FROM ligne_facturation lf
        JOIN produit p ON lf.produitID = p.produitID
        JOIN famille fam ON p.familleID = fam.familleID
        GROUP BY fam.FAMILLE_NAME, Month
        ORDER BY Month, fam.FAMILLE_NAME
    ";
    $revenueStmt = $pdo->prepare($revenueSql);
    $revenueStmt->execute();
    $revenueData = $revenueStmt->fetchAll(PDO::FETCH_ASSOC);

    $jsonRevenueData = json_encode($revenueData);

    $avgPricePerVolumeSql = "
        SELECT p.NOM_PRODUIT, AVG(lf.prix / lf.volume) AS AvgPricePerVolume
        FROM produit p
        JOIN ligne_facturation lf ON p.produitID = lf.produitID
        GROUP BY p.NOM_PRODUIT;
    ";
    $avgPricePerVolumeStmt = $pdo->prepare($avgPricePerVolumeSql);
    $avgPricePerVolumeStmt->execute();
    $avgPricePerVolumeData = $avgPricePerVolumeStmt->fetchAll(PDO::FETCH_ASSOC);

    $jsonAvgPricePerVolumeData = json_encode($avgPricePerVolumeData);
    ?>

    <header>
        <div class="burger-button" onclick="toggleAsideMenu()">
            <button class="fixed p-3 -pb-1 z-50 right-6 top-3 rounded-md shadow-lg md:hidden bg-[#301014]">
                <svg class="w-6 h-6 text-[#E8D8C4]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>
    </header>

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
                        <li>
                            <a href="#fourthgraph" class="flex bg-white hover:bg-[#E8D8C4] rounded-xl font-bold text-sm text-gray-900 py-3 px-4">
                                <svg width="16" height="16" class="text-lg mr-4" fill="#000000" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512 512" enable-background="new 0 0 512 512" xml:space="preserve">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <g id="1adf468c34277fe2a9aa3ee4b9004872">
                                            <path display="inline" d="M335.977,34.634l-160.603-0.479L0.5,335.183l78.006,142.662l349.025-0.066L511.5,333.731L335.977,34.634z M161.643,334.717l93.297-160.598l90.98,160.078L161.643,334.717z"> </path>
                                        </g>
                                    </g>
                                </svg>Quatrième graphique
                            </a>
                        </li>
                        <li>
                            <a href="#fifthgraph" class="flex bg-white hover:bg-[#E8D8C4] rounded-xl font-bold text-sm text-gray-900 py-3 px-4">
                                <svg widht="16" height="16" class="text-lg mr-4" fill="#000000" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <path d="M8 3C7.449219 3 7 3.449219 7 4L7 30C7 30.554688 7.449219 31 8 31L13 31C13.554688 31 14 30.554688 14 30L14 4C14 3.449219 13.554688 3 13 3 Z M 17 3C16.449219 3 16 3.449219 16 4L16 30C16 30.554688 16.449219 31 17 31L22 31C22.554688 31 23 30.554688 23 30L23 4C23 3.449219 22.554688 3 22 3 Z M 9.5 7L11.5 7C12.054688 7 12.5 7.449219 12.5 8C12.5 8.550781 12.054688 9 11.5 9L9.5 9C8.945313 9 8.5 8.550781 8.5 8C8.5 7.449219 8.945313 7 9.5 7 Z M 18.5 7L20.5 7C21.054688 7 21.5 7.449219 21.5 8C21.5 8.550781 21.054688 9 20.5 9L18.5 9C17.945313 9 17.5 8.550781 17.5 8C17.5 7.449219 17.945313 7 18.5 7 Z M 37.59375 8.4375L32.75 9.59375C32.492188 9.65625 32.265625 9.804688 32.125 10.03125C32.066406 10.125 32.023438 10.238281 32 10.34375L32 10C32 9.449219 31.554688 9 31 9L26 9C25.449219 9 25 9.449219 25 10L25 30C25 30.554688 25.449219 31 26 31L31 31C31.554688 31 32 30.554688 32 30L32 10.78125L36.8125 30.25C36.921875 30.710938 37.328125 31.03125 37.78125 31.03125C37.855469 31.03125 37.921875 31.015625 38 31L42.875 29.84375C43.132813 29.78125 43.359375 29.632813 43.5 29.40625C43.640625 29.179688 43.6875 28.914063 43.625 28.65625L38.8125 9.1875C38.683594 8.648438 38.128906 8.308594 37.59375 8.4375 Z M 36.96875 12.78125C37.351563 12.847656 37.6875 13.128906 37.78125 13.53125C37.90625 14.070313 37.566406 14.625 37.03125 14.75L36.0625 14.96875C35.984375 14.988281 35.917969 15 35.84375 15C35.390625 15 34.953125 14.679688 34.84375 14.21875C34.71875 13.679688 35.058594 13.15625 35.59375 13.03125L36.5625 12.8125C36.695313 12.78125 36.839844 12.757813 36.96875 12.78125 Z M 28 13L29 13C29.554688 13 30 13.449219 30 14C30 14.550781 29.554688 15 29 15L28 15C27.445313 15 27 14.550781 27 14C27 13.449219 27.445313 13 28 13 Z M 39.53125 24.46875C40.066406 24.347656 40.621094 24.679688 40.75 25.21875C40.875 25.753906 40.539063 26.28125 40 26.40625L39.03125 26.65625C38.953125 26.675781 38.855469 26.6875 38.78125 26.6875C38.328125 26.6875 37.921875 26.367188 37.8125 25.90625C37.6875 25.371094 38.023438 24.8125 38.5625 24.6875 Z M 9.5 25L11.5 25C12.054688 25 12.5 25.449219 12.5 26C12.5 26.550781 12.054688 27 11.5 27L9.5 27C8.945313 27 8.5 26.550781 8.5 26C8.5 25.449219 8.945313 25 9.5 25 Z M 18.5 25L20.5 25C21.054688 25 21.5 25.449219 21.5 26C21.5 26.550781 21.054688 27 20.5 27L18.5 27C17.945313 27 17.5 26.550781 17.5 26C17.5 25.449219 17.945313 25 18.5 25 Z M 28 25L29 25C29.554688 25 30 25.445313 30 26C30 26.554688 29.554688 27 29 27L28 27C27.445313 27 27 26.554688 27 26C27 25.445313 27.445313 25 28 25 Z M 3 32C2.449219 32 2 32.445313 2 33L2 36C2 36.554688 2.449219 37 3 37L47 37C47.554688 37 48 36.554688 48 36L48 33C48 32.445313 47.554688 32 47 32 Z M 6 39L6 44.5C6 45.878906 7.121094 47 8.5 47C9.878906 47 11 45.878906 11 44.5L11 39 Z M 39 39L39 44.5C39 45.878906 40.121094 47 41.5 47C42.878906 47 44 45.878906 44 44.5L44 39Z"></path>
                                    </g>
                                </svg>Cinquième graphique
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
            <div id="fourthgraph" class="mt-6 bg-white text-gray-900 rounded-lg shadow-lg p-3">
                <h2 class="text-xl text-center font-semibold leading-none mb-4 bg-[#561C24] rounded-xl shadow-md text-[#E8D8C4] py-3 px-4">
                    Revenu par mois et famille de produits
                </h2>
                <div id="revenue-chart" class="chart-container"></div>
            </div>
            <div id="fifthgraph" class="mt-6 bg-white text-gray-900 rounded-lg shadow-lg p-3">
                <h2 class="text-xl text-center font-semibold leading-none mb-4 bg-[#561C24] rounded-xl shadow-md text-[#E8D8C4] py-3 px-4">
                    Prix moyen par volume pour chaque produit
                </h2>
                <div id="avg-price-per-volume-chart" class="chart-container"></div>
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

    <!-- Quatrième graphique -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var revenueData = <?php echo $jsonRevenueData; ?>;
            var seriesData = {};
            var series = [];
            var categories = [];

            revenueData.forEach(function(dataPoint) {
                if (!seriesData[dataPoint.FAMILLE_NAME]) {
                    seriesData[dataPoint.FAMILLE_NAME] = [];
                }
                var monthIndex = categories.indexOf(dataPoint.Month);
                if (monthIndex === -1) {
                    categories.push(dataPoint.Month);
                    monthIndex = categories.length - 1;
                }

                while (seriesData[dataPoint.FAMILLE_NAME].length < categories.length) {
                    seriesData[dataPoint.FAMILLE_NAME].push(0);
                }

                seriesData[dataPoint.FAMILLE_NAME][monthIndex] = parseFloat(dataPoint.TotalRevenue);
            });

            Object.keys(seriesData).forEach(function(name) {
                series.push({
                    name: name,
                    data: seriesData[name]
                });
            });

            var revenueOptions = {
                series: series,
                chart: {
                    height: 400,
                    type: 'bar',
                    stacked: false,
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                    },
                },
                stroke: {
                    width: 1,
                    colors: ['#fff']
                },
                title: {
                    text: 'Revenu mensuel par famille de produits'
                },
                dataLabels: {
                    enabled: false
                },
                xaxis: {
                    categories: categories,
                    title: {
                        text: 'Mois'
                    }
                },
                yaxis: {
                    title: {
                        text: 'Revenu total'
                    },
                    labels: {
                        formatter: function(value) {
                            return value.toLocaleString('fr-FR') + " €";
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return val.toLocaleString('fr-FR') + " €";
                        }
                    }
                },
                fill: {
                    opacity: 1
                },
                legend: {
                    position: 'right',
                    offsetY: 40
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        legend: {
                            position: 'bottom',
                            offsetX: -10,
                            offsetY: 0
                        }
                    }
                }]
            };

            var revenueChart = new ApexCharts(document.querySelector("#revenue-chart"), revenueOptions);
            revenueChart.render();
        });
    </script>

    <!-- Cinquième graphique -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var avgPricePerVolumeData = <?php echo $jsonAvgPricePerVolumeData; ?>;
            var options = {
                series: [{
                    name: 'Prix moyen par volume',
                    data: avgPricePerVolumeData.map(function(data) {
                        return data.AvgPricePerVolume;
                    })
                }],
                chart: {
                    height: 350,
                    type: 'bar'
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        horizontal: true,
                    }
                },
                dataLabels: {
                    enabled: false
                },
                xaxis: {
                    categories: avgPricePerVolumeData.map(function(data) {
                        return data.NOM_PRODUIT;
                    }),
                },
                yaxis: {
                    title: {
                        text: 'Prix moyen par volume (€)'
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return val.toLocaleString('fr-FR', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }) + " €";
                        }
                    }
                }
            };

            var chart = new ApexCharts(document.querySelector("#avg-price-per-volume-chart"), options);
            chart.render();
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