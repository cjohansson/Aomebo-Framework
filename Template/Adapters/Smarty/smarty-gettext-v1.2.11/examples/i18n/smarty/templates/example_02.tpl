{locale path=$path domain='messages'}

This example includes another template that uses THE SAME text-domain as is used within this template. If you have a look at the included file, you will see that there IS NO 'locale' Smarty function all at the top.

RUNNING THE EXAMPLE:
php example_02.php [locale]

{"This is my first translation message"|gettext}
{"This is my yet another translation message with a custom modifier"|gettext|substr:0:60}
{"%d comment"|ngettext:"%d comments":1|sprintf:1}
---INCLUDED FILE START---
{include file='example_included_file_with_same_domain.tpl'}
---INCLUDED FILE END---
{"%d comment"|ngettext:"%d comments":0|sprintf:0}
{"%d comment"|ngettext:"%d comments":10|sprintf:10}
