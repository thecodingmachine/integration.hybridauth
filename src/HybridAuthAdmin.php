<?php
use Mouf\MoufManager;
use Mouf\MoufUtils;

$moufManager->declareComponent('hybridauthinstall', 'Mouf\\Integration\\HybridAuth\\Controllers\\HybridAuthInstallController', true);
$moufManager->bindComponents('hybridauthinstall', 'template', 'moufInstallTemplate');
$moufManager->bindComponents('hybridauthinstall', 'content', 'block.content');

