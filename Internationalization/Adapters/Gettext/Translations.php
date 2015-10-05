<?php
/**
 * Aomebo - a module-based MVC framework for PHP 5.3 and higher
 *
 * Copyright 2010 - 2015 by Christian Johansson <christian@cvj.se>
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 * @license LGPL version 3
 * @see http://www.aomebo.org/ or https://github.com/cjohansson/Aomebo-Framework
 */

/**
 *
 */
namespace Aomebo\Internationalization\Adapters\Gettext
{

    /**
     *
     */
    class Translations
    {

        /**
         * @var array
         */
        public $entries = array();

        /**
         * @var array
         */
        public $headers = array();

        /**
         * Add entry to the PO structure
         *
         * @param object &$entry
         * @return bool true on success, false if the entry doesn't have a key
         */
        public function add_entry($entry)
        {
            if (is_array($entry)) {
                $entry = new Translation_Entry($entry);
            }
            $key = $entry->key();
            if (false === $key) return false;
            $this->entries[$key] = &$entry;
            return true;
        }

        /**
         * @param $entry
         * @return bool
         */
        public function add_entry_or_merge($entry)
        {
            if (is_array($entry)) {
                $entry = new Translation_Entry($entry);
            }
            $key = $entry->key();
            if (false === $key) return false;
            if (isset($this->entries[$key])) {

                $translationEntry = & $this->entries[$key];
                /** @var Translation_Entry $translationEntry */

                $translationEntry->merge_with($entry);

            } else {
                $this->entries[$key] = &$entry;
            }
            return true;
        }

        /**
         * Sets $header PO header to $value
         *
         * If the header already exists, it will be overwritten
         *
         * TODO: this should be out of this class, it is gettext specific
         *
         * @param string $header header name, without trailing :
         * @param string $value header value, without trailing \n
         */
        public function set_header($header, $value)
        {
            $this->headers[$header] = $value;
        }

        /**
         * @param $headers
         */
        public function set_headers($headers)
        {
            foreach($headers as $header => $value) {
                $this->set_header($header, $value);
            }
        }

        /**
         * @param $header
         * @return bool
         */
        public function get_header($header)
        {
            return isset($this->headers[$header])? $this->headers[$header] : false;
        }

        /**
         * @param Translation_Entry $entry
         * @return Translation_Entry|bool
         */
        public function translate_entry(& $entry)
        {
            $key = $entry->key();
            return isset($this->entries[$key])? $this->entries[$key] : false;
        }

        /**
         * @param $singular
         * @param null $context
         * @return mixed
         */
        public function translate($singular, $context = null)
        {
            $entry = new Translation_Entry(array('singular' => $singular, 'context' => $context));
            $translated = $this->translate_entry($entry);
            return ($translated && !empty($translated->translations))? $translated->translations[0] : $singular;
        }

        /**
         * Given the number of items, returns the 0-based index of the plural form to use
         *
         * Here, in the base Translations class, the common logic for English is implemented:
         * 	0 if there is one element, 1 otherwise
         *
         * This function should be overrided by the sub-classes. For example MO/PO can derive the logic
         * from their headers.
         *
         * @param integer $count number of items
         * @return int
         */
        public function select_plural_form($count)
        {
            return 1 == $count? 0 : 1;
        }

        /**
         * @return int
         */
        public function get_plural_forms_count()
        {
            return 2;
        }

        /**
         * @param $singular
         * @param $plural
         * @param $count
         * @param null $context
         * @return mixed
         */
        public function translate_plural($singular, $plural, $count, $context = null)
        {
            $entry = new Translation_Entry(array('singular' => $singular, 'plural' => $plural, 'context' => $context));
            $translated = $this->translate_entry($entry);
            $index = $this->select_plural_form($count);
            $total_plural_forms = $this->get_plural_forms_count();
            if ($translated && 0 <= $index && $index < $total_plural_forms &&
                    is_array($translated->translations) &&
                    isset($translated->translations[$index]))
                return $translated->translations[$index];
            else
                return 1 == $count? $singular : $plural;
        }

        /**
         * Merge $other in the current object.
         *
         * @param Translations $other
         * @return void
         **/
        public function merge_with(& $other)
        {
            foreach($other->entries as $entry)
            {
                /** @var Translation_Entry $entry */
                $this->entries[$entry->key()] = $entry;
            }
        }

        /**
         * @param Translations $other
         */
        public function merge_originals_with(& $other)
        {
            foreach ($other->entries as $entry)
            {
                /** @var Translation_Entry $entry */
                if (!isset($this->entries[$entry->key()])) {
                    $this->entries[$entry->key()] = $entry;
                } else {

                    $translationEntry = & $this->entries[$entry->key()];

                    /** @var Translation_Entry $translationEntry */
                    $translationEntry->merge_with($entry);

                }
            }
        }

    }

}
