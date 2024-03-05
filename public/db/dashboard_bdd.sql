-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 29 jan. 2024 à 13:59
-- Version du serveur : 10.4.28-MariaDB
-- Version de PHP : 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `tableau_bord`
--

-- --------------------------------------------------------

--
-- Structure de la table `application`
--

CREATE TABLE `application` (
  `IRT` varchar(10) NOT NULL,
  `nomAppli` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `centresactivite`
--

CREATE TABLE `centresactivite` (
  `CentreActiviteID` int(11) NOT NULL,
  `NumeroCentreActivite` bigint(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `clients`
--

CREATE TABLE `clients` (
  `ClientID` int(11) NOT NULL,
  `NomClient` varchar(255) NOT NULL,
  `GrandClientID` int(11) DEFAULT NULL,
  `CentreActiviteID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `famille`
--

CREATE TABLE `famille` (
  `familleID` int(11) NOT NULL,
  `FAMILLE_NAME` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `grandclients`
--

CREATE TABLE `grandclients` (
  `GrandClientID` int(11) NOT NULL,
  `NomGrandClient` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ligne_facturation`
--

CREATE TABLE `ligne_facturation` (
  `LF_ID` int(11) NOT NULL,
  `produitID` int(11) NOT NULL,
  `CentreActiviteID` int(11) NOT NULL,
  `mois` date NOT NULL,
  `IRT` varchar(10) NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `nature` varchar(30) NOT NULL,
  `volume` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `produit`
--

CREATE TABLE `produit` (
  `produitID` int(11) NOT NULL,
  `NOM_PRODUIT` varchar(50) NOT NULL,
  `familleID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `application`
--
ALTER TABLE `application`
  ADD PRIMARY KEY (`IRT`);

--
-- Index pour la table `centresactivite`
--
ALTER TABLE `centresactivite`
  ADD PRIMARY KEY (`CentreActiviteID`);

--
-- Index pour la table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`ClientID`),
  ADD KEY `GrandClientID` (`GrandClientID`),
  ADD KEY `CentreActiviteID` (`CentreActiviteID`);

--
-- Index pour la table `famille`
--
ALTER TABLE `famille`
  ADD PRIMARY KEY (`familleID`);

--
-- Index pour la table `grandclients`
--
ALTER TABLE `grandclients`
  ADD PRIMARY KEY (`GrandClientID`),
  ADD UNIQUE KEY `NomGrandClient` (`NomGrandClient`);

--
-- Index pour la table `ligne_facturation`
--
ALTER TABLE `ligne_facturation`
  ADD PRIMARY KEY (`LF_ID`),
  ADD KEY `LF_ibfk_1` (`produitID`),
  ADD KEY `LF_ibfk_2` (`CentreActiviteID`),
  ADD KEY `LF_ibfk_3` (`IRT`);

--
-- Index pour la table `produit`
--
ALTER TABLE `produit`
  ADD PRIMARY KEY (`produitID`),
  ADD KEY `produit_ibfk_1` (`familleID`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `centresactivite`
--
ALTER TABLE `centresactivite`
  MODIFY `CentreActiviteID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `clients`
--
ALTER TABLE `clients`
  MODIFY `ClientID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `famille`
--
ALTER TABLE `famille`
  MODIFY `familleID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `grandclients`
--
ALTER TABLE `grandclients`
  MODIFY `GrandClientID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `ligne_facturation`
--
ALTER TABLE `ligne_facturation`
  MODIFY `LF_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `produit`
--
ALTER TABLE `produit`
  MODIFY `produitID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `clients_ibfk_1` FOREIGN KEY (`GrandClientID`) REFERENCES `grandclients` (`GrandClientID`),
  ADD CONSTRAINT `clients_ibfk_2` FOREIGN KEY (`CentreActiviteID`) REFERENCES `centresactivite` (`CentreActiviteID`);

--
-- Contraintes pour la table `ligne_facturation`
--
ALTER TABLE `ligne_facturation`
  ADD CONSTRAINT `LF_ibfk_1` FOREIGN KEY (`produitID`) REFERENCES `produit` (`produitID`),
  ADD CONSTRAINT `LF_ibfk_2` FOREIGN KEY (`CentreActiviteID`) REFERENCES `centresactivite` (`CentreActiviteID`),
  ADD CONSTRAINT `LF_ibfk_3` FOREIGN KEY (`IRT`) REFERENCES `application` (`IRT`);

--
-- Contraintes pour la table `produit`
--
ALTER TABLE `produit`
  ADD CONSTRAINT `produit_ibfk_1` FOREIGN KEY (`familleID`) REFERENCES `famille` (`familleID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
