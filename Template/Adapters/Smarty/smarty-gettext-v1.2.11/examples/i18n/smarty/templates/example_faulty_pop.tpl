{locale path=$path domain='messages'}

This example includes another template that uses A DIFFERENT text-domain as is used within this template. If you have a look at the included file, you will see that there IS A 'locale' Smarty function call at the top.

IN THIS EXAMPLE WE DO NOT POP AT THE END OF THE INCLUDED TEMPLATE. YOU WILL SEE THAT THE REMAINDER OF THE INCLUDING TEMPLATE WILL NOT BE TRANSLATED, I.E. THE STRING IN THE TEMPLATE WILL BE ECHOED WITHOUT TRANSLATION

RUNNING THE EXAMPLE:
php example_faulty_pop.php [locale]


{"This is my first translation message"|gettext}
{"This is my yet another translation message with a custom modifier"|gettext|substr:0:60}
{"%d comment"|ngettext:"%d comments":1|sprintf:1}
---INCLUDED FILE START---
{include file='example_included_file_with_different_domain_without_pop.tpl'}
---INCLUDED FILE END---
{"%d comment"|ngettext:"%d comments":0|sprintf:0}
{"%d comment"|ngettext:"%d comments":10|sprintf:10}
