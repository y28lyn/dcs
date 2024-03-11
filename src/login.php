<!DOCTYPE html>
<html x-data="data()" lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="../public/css/style.css" rel="stylesheet" />
    <link rel="icon" href="../public/dcs_icon.png" />
    <title>Dashboard DCS</title>
</head>

<body class="relative bg-[#E8D8C4] overflow-hidden max-h-screen">
    <main class="relative">
        <section class="bg-[#E8D8C4] overflow-hidden">
            <div class="flex flex-col lg:flex-row lg:items-stretch h-screen">
                <div class="relative flex items-center justify-center w-full lg:order-2 lg:w-7/12">
                    <div class="relative px-4 pt-24 pb-16 text-center sm:px-6 md:px-24 2xl:px-32 lg:py-24 lg:text-left">
                        <h1 class="text-4xl text-[#561C24] font-bold sm:text-6xl xl:text-8xl">
                            Projet<br />
                            DCS
                        </h1>
                        <p class="mt-4 text-xl text-[#6D2932]">L'objectif de DCS est de créer une solution d'hébergement informatique et de réaliser une analyse des coûts et des métriques associées.</p>

                        <a href="main.php" class="inline-block px-4 py-4 mt-4 font-semibold text-white transition-all duration-200 bg-[#6D2932] border border-transparent rounded-md hover:bg-[#561C24] hover:scale-105 focus:bg-[#561C24]">
                            Voir les analyses
                        </a>
                    </div>
                </div>

                <div class="relative w-full overflow-hidden lg:order-1 h-screen lg:w-5/12">
                    <div class="absolute inset-0">
                        <img class="object-cover w-full h-full scale-150" src="https://images.unsplash.com/photo-1550025899-5f8a06b1b3a8?q=80&w=1887&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="" />
                    </div>

                    <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent"></div>

                    <div class="absolute bottom-0 left-0">
                        <div class="p-4 sm:p-6 lg:p-8">
                            <h2 class="font-bold text-[#E8D8C4] text-3xl">Fait par</h2>
                            <p class="max-w-xs mt-1.5 text-xl text-[#E8D8C4]">le Lycée Jacques Brel</p>
                            <p class="max-w-md mt-1.5 text-[10px] text-[#E8D8C4]">BEIRADE Ilyes, SCHER Aloys, BEN HADJ AMOR Jenna, MOLLARET Thomas</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script src="https://cdn.tailwindcss.com"></script>
</body>