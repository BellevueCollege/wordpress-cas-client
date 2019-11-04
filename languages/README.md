Translation how-to
==================

The wpcasldap.pot file
---------------------------------
This file contain the strings to translate. It could be generated using the _xgettext_ command.

To generate _wpcasldap.pot_ file, please run the following command __in the plugin root directory__ :

    xgettext    --from-code utf-8 \
                -o "languages/wpcasldap.pot" \
                --omit-header \
                --copyright-holder="Bellevue College" \
                --keyword="__" \
                --keyword="_e" \
                $( find -name "*.php" )

The wpcasldap-xx_YY.po and wpcasldap-xx_YY.mo files
-------------------------------------------------------------------
This files contains the translated strings for a specific language.
The _MO_ files are the compiled version of the _PO_ files.
These files could be created using tool like [poedit](https://poedit.net/).
