<?php

declare(strict_types=1);

namespace SlaveMarket\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200516055147 extends AbstractMigration {
    public function getDescription(): string {
        return 'Создание таблицы рабов ';
    }

    public function up(Schema $schema): void {
        $this->addSlave();
        $this->addCategory();
        $this->addSlaveCategory();
    }

    public function down(Schema $schema): void {
        $this->addSql('DROP TABLE slave_category');

        $this->addSql('DROP TABLE slave');
        $this->addSql('DROP SEQUENCE slave_id_seq ');

        $this->addSql('DROP TABLE category');
        $this->addSql('DROP SEQUENCE category_id_seq ');
    }

    protected function addCategory(): void {
        $sql = <<<SQL
CREATE SEQUENCE category_id_seq START 1;
SQL;
        $this->addSql($sql);
        $sql = <<<SQL
CREATE TABLE category (
 id INTEGER PRIMARY KEY DEFAULT nextval('category_id_seq'),
 title VARCHAR(40) NOT NULL,
 nested_left INTEGER NOT NULL,
 nested_right INTEGER NOT NULL
);
SQL;
        $this->addSql($sql);

        $sql = <<< SQL
CREATE UNIQUE INDEX category_nested_left_nested_right_ind_uniq
  ON category (nested_left, nested_right);
SQL;
        $this->addSql($sql);

        $sql = <<< SQL
CREATE UNIQUE INDEX category_nested_left_idx_unq
  ON category (nested_left)
SQL;
        $this->addSql($sql);

        $sql = <<< SQL
CREATE UNIQUE INDEX category_nested_right_idx_unq
  ON category (nested_right)
SQL;
        $this->addSql($sql);
    }

    protected function addSlave(): void {
        $sql = <<<SQL
CREATE SEQUENCE slave_id_seq START 1;
SQL;
        $this->addSql($sql);

        $sql = <<<SQL
CREATE TABLE slave (
 id INTEGER PRIMARY KEY DEFAULT nextval('slave_id_seq'),
 name VARCHAR(40) NOT NULL,
 sex INTEGER NOT NULL,
 weight INTEGER NOT NULL,
 skin_color integer NOT NULL,
 homeland VARCHAR(40) NOT NULL,
 description text,
 price_rent float,
 price_buy float 
);
SQL;
        $this->addSql($sql);

        $sql = <<< SQL
CREATE INDEX slave_price_rent_idx
  ON slave (price_rent)
SQL;
        $this->addSql($sql);

        $sql = <<< SQL
CREATE INDEX slave_price_buy_idx
  ON slave (price_buy)
SQL;
        $this->addSql($sql);

        $sql = <<< SQL
CREATE INDEX slave_weight_idx
  ON slave (weight)
SQL;
        $this->addSql($sql);

        $sql = <<< SQL
CREATE INDEX slave_sex_idx
  ON slave (weight)
SQL;
        $this->addSql($sql);
    }

    protected function addSlaveCategory(): void {
        $sql = <<<SQL
CREATE TABLE slave_category (
 slave_id INTEGER NOT NULL,
 category_id INTEGER NOT NULL,
  PRIMARY KEY (slave_id, category_id),
  FOREIGN KEY (slave_id) REFERENCES slave (id),
  FOREIGN KEY (category_id) REFERENCES category (id)
);
SQL;
        $this->addSql($sql);
    }
}
