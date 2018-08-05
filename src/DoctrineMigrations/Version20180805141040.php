<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180805141040 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql("CREATE TABLE `event` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`aggregate_id` VARCHAR(255) NOT NULL COLLATE 'utf8_unicode_ci',
	`aggregate_name` VARCHAR(255) NOT NULL COLLATE 'utf8_unicode_ci',
	`created_at` DATETIME NOT NULL,
	`event` LONGTEXT NOT NULL COLLATE 'utf8_unicode_ci',
	PRIMARY KEY (`id`),
	INDEX `aggregate_index` (`aggregate_id`, `aggregate_name`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
;

        ");

    }

    public function down(Schema $schema) : void
    {
        $this->addSql("DROP TABLE `event`");

    }
}
