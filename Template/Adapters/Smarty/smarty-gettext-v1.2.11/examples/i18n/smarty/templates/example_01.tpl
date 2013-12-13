{locale path=$path domain='messages'}

This is a very simple example that does nothing else but translate a few messages. It sets the locale at the beginning. If you use the standard text-domain, i.e. 'messages', you can also remove the domain declaration for the 'locale' Smarty function call in line 1 of this example.

RUNNING THE EXAMPLE:
php example_01.php [locale]

{"This is my first translation message"|gettext}
{"This is my yet another translation message with a custom modifier"|gettext|substr:0:60}
{"%d comment"|ngettext:"%d comments":1|sprintf:1}
{"%d comment"|ngettext:"%d comments":0|sprintf:0}
{"%d comment"|ngettext:"%d comments":10|sprintf:10}
