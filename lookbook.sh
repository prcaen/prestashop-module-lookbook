#!/bin/bash

# Variables
path_website='/Users/Nyx/Sites/bellam/'
theme='bellam'

## Delete old
# Root
sudo rm $path_website/look.php
sudo rm $path_website/lookbook.php
# Controllers
sudo rm $path_website/controllers/LookbookController.php
sudo rm $path_website/controllers/LookController.php
# TPL
sudo rm $path_website/themes/$theme/look.tpl
sudo rm $path_website/themes/$theme/lookbook.tpl

## Create Symlinks
# Root
sudo ln -s $path_website/modules/lookbook/frontend/look.php $path_website/look.php
sudo ln -s $path_website/modules/lookbook/frontend/lookbook.php $path_website/lookbook.php
# Controllers
sudo ln -s $path_website/modules/lookbook/frontend/controllers/LookController.php $path_website/controllers/LookController.php
sudo ln -s $path_website/modules/lookbook/frontend/controllers/LookbookController.php $path_website/controllers/LookbookController.php
# TPL
sudo ln -s $path_website/modules/lookbook/frontend/look.tpl $path_website/themes/$theme/look.tpl
sudo ln -s $path_website/modules/lookbook/frontend/lookbook.tpl $path_website/themes/$theme/lookbook.tpl

## CHMOD
# Root
sudo chmod 777 $path_website/look.php $path_website/lookbook.php
# Controllers
sudo chmod 777 $path_website/controllers/LookController.php $path_website/controllers/LookbookController.php
# TPL
sudo chmod 777 $path_website/themes/$theme/look.tpl $path_website/themes/$theme/lookbook.tpl