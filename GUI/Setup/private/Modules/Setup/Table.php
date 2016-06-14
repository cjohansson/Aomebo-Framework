<?php
/**
 *
 */

/**
 *
 */
namespace Modules\Setup\TableColumns
{

    /**
     *
     */
    class Id extends \Aomebo\Database\Adapters\TableColumn
    {

        /**
         *
         */
        public function __construct()
        {
            parent::__construct(
                'id',
                'INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT',
                false
            );
        }

    }

    /**
     *
     */
    class Name extends \Aomebo\Database\Adapters\TableColumn
    {

        /**
         *
         */
        public function __construct()
        {
            parent::__construct(
                'name',
                'VARCHAR(100) NOT NULL DEFAULT ""',
                true
            );
        }

    }

    /**
     *
     */
    class Cash extends \Aomebo\Database\Adapters\TableColumn
    {

        /**
         *
         */
        public function __construct()
        {
            parent::__construct(
                'cash',
                'INT(10) NOT NULL DEFAULT 0',
                false
            );
        }

    }

}


/**
 *
 */
namespace Modules\Setup
{

    /**
     * @method static \Modules\Setup\Table getInstance()
     */
    class Table extends \Aomebo\Database\Adapters\Table
    {

        /**
         * @var TableColumns\Id
         */
        public $id;

        /**
         * @var TableColumns\Name
         */
        public $name;

        /**
         *
         */
        public function __construct()
        {

            parent::__construct(
                'test',
                'ENGINE=MyISAM DEFAULT CHARSET={DATA CHARSET} DEFAULT COLLATE={COLLATE CHARSET}'
            );

            $this->id = new TableColumns\Id();
            $this->name = new TableColumns\Name();
            $this->cash = new TableColumns\Cash();

        }

    }

}
