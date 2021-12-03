README.txt for Jugaad Product module
---------------------------

CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation

 INTRODUCTION
 ------------
  - Custom module that helps to add product content type and custom block for QR code in right sidebar.
    By Scanner the QR code you would redirect to the App Purchase Link.


 REQUIREMENTS
 ------------
  - Install aferrandini/phpqrcode package from packagist.org 
    composer require aferrandini/phpqrcode.


 INSTALLATION
 ------------

  - Install the Jugaad Patches module as you would normally install a
  contributed Drupal module. Visit https://www.drupal.org/node/1897420 for
  further information.

  - Add below snippet in your main composer.json file and run the
      composer require drupal/jugaad_patch:dev-main to download the module
    and it's dependencies.
      "repositories": [
              {
                  "type": "vcs",
                  "url": "git@github.com:divyamehtani15/jugaad-patches"
              }
          ],