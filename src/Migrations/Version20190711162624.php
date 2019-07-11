<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190711162624 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE billiard_game (id INT AUTO_INCREMENT NOT NULL, winner_user_id INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, winner INT NOT NULL, INDEX IDX_F90A32114B217B3A (winner_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE billiard_game_user (billiard_game_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_1FFDF7687600B1E1 (billiard_game_id), INDEX IDX_1FFDF768A76ED395 (user_id), PRIMARY KEY(billiard_game_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE table_tennis_game (id INT AUTO_INCREMENT NOT NULL, winner_user_id INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, winner INT NOT NULL, INDEX IDX_5B9884784B217B3A (winner_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE table_tennis_game_user (table_tennis_game_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_D16041E79D98BABA (table_tennis_game_id), INDEX IDX_D16041E7A76ED395 (user_id), PRIMARY KEY(table_tennis_game_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE billiard_elo_history (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, created_at DATETIME NOT NULL, elo_rating INT NOT NULL, INDEX IDX_89165559A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE billiard_stats (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, elo_rating INT NOT NULL, nb_won INT DEFAULT NULL, nb_lost INT DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_D923A950A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE table_tennis_stats (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, elo_rating INT DEFAULT NULL, nb_won INT DEFAULT NULL, nb_lost INT DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_EDEFE21AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE table_tennis_elo_history (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, created_at DATETIME NOT NULL, elo_rating INT NOT NULL, INDEX IDX_170F09F9A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE billiard_game ADD CONSTRAINT FK_F90A32114B217B3A FOREIGN KEY (winner_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE billiard_game_user ADD CONSTRAINT FK_1FFDF7687600B1E1 FOREIGN KEY (billiard_game_id) REFERENCES billiard_game (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE billiard_game_user ADD CONSTRAINT FK_1FFDF768A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE table_tennis_game ADD CONSTRAINT FK_5B9884784B217B3A FOREIGN KEY (winner_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE table_tennis_game_user ADD CONSTRAINT FK_D16041E79D98BABA FOREIGN KEY (table_tennis_game_id) REFERENCES table_tennis_game (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE table_tennis_game_user ADD CONSTRAINT FK_D16041E7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE billiard_elo_history ADD CONSTRAINT FK_89165559A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE billiard_stats ADD CONSTRAINT FK_D923A950A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE table_tennis_stats ADD CONSTRAINT FK_EDEFE21AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE table_tennis_elo_history ADD CONSTRAINT FK_170F09F9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('DROP TABLE game_set');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE billiard_game_user DROP FOREIGN KEY FK_1FFDF7687600B1E1');
        $this->addSql('ALTER TABLE table_tennis_game_user DROP FOREIGN KEY FK_D16041E79D98BABA');
        $this->addSql('CREATE TABLE game_set (id INT AUTO_INCREMENT NOT NULL, game_id INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, score1 INT NOT NULL, score2 INT NOT NULL, INDEX IDX_FD4E3619E48FD905 (game_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE game_set ADD CONSTRAINT FK_FD4E3619E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('DROP TABLE billiard_game');
        $this->addSql('DROP TABLE billiard_game_user');
        $this->addSql('DROP TABLE table_tennis_game');
        $this->addSql('DROP TABLE table_tennis_game_user');
        $this->addSql('DROP TABLE billiard_elo_history');
        $this->addSql('DROP TABLE billiard_stats');
        $this->addSql('DROP TABLE table_tennis_stats');
        $this->addSql('DROP TABLE table_tennis_elo_history');
    }
}
