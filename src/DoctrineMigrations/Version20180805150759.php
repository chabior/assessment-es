<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180805150759 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("CREATE TABLE `player_projection` (
	`player_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`real_money` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`player_id`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
;
");

        $this->addSql("CREATE TABLE `player_bonus_projection` (
	`wallet_id` VARCHAR(255) NOT NULL COLLATE 'utf8_unicode_ci',
	`player_id` INT(10) UNSIGNED NOT NULL,
	`money` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`wallet_id`),
	INDEX `player_id` (`player_id`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
;
");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE player_projection');

        $this->addSql('DROP TABLE player_bonus_projection');
    }
}
