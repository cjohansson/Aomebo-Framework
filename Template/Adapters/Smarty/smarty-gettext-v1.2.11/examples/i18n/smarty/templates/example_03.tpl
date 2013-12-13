{locale path=$path domain='messages'}

This example includes another template that uses A DIFFERENT text-domain as is used within this template. If you have a look at the included file, you will see that there IS A 'locale' Smarty function call at the top.

RUNNING THE EXAMPLE:
php example_03.php [locale]

REMEMBER:
  -- We have to remember to POP the locale at the end of the included file so we get back the original 'domain'. 
  -- We can do this anywhere at the end of the file.
  -- If you don't do it the file that included this template will try to use the same 'domain' and you'll most likely get odd results. (see example_faulty_pop.php)

{"This is my first translation message"|gettext}
{"This is my yet another translation message with a custom modifier"|gettext|substr:0:60}
{"%d comment"|ngettext:"%d comments":1|sprintf:1}
---INCLUDED FILE START---
{include file='example_included_file_with_different_domain.tpl'}
---INCLUDED FILE END---
{"%d comment"|ngettext:"%d comments":0|sprintf:0}
{"%d comment"|ngettext:"%d comments":10|sprintf:10}
