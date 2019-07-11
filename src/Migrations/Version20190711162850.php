<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190711162850 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('INSERT cftischtennis.table_tennis_stats(user_id, elo_rating, nb_won, nb_lost, created_at) (SELECT id, elo_rating, nb_won, nb_lost, NOw() FROM user);');
        $this->addSql('insert cftischtennis.table_tennis_elo_history (SELECT * FROM elo_history)');
        $this->addSql('insert cftischtennis.table_tennis_game (SELECT * FROM game)');
        $this->addSql('insert cftischtennis.table_tennis_game_user (SELECT * FROM game_user)');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
