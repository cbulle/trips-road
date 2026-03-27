<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class Initial extends BaseMigration
{
    /**
     * Up Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-up-method
     *
     * @return void
     */
    public function up(): void
    {
        $this->table('comments')
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('roadtrip_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => true,
            ])
            ->addColumn('points_of_interest_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => true,
            ])
            ->addColumn('body', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('rating', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('user_id')
                    ->setName('id_utilisateur')
            )
            ->addIndex(
                $this->index('roadtrip_id')
                    ->setName('id_roadtrip')
            )
            ->addIndex(
                $this->index('points_of_interest_id')
                    ->setName('id_poi')
            )
            ->create();

        $this->table('conversations')
            ->addColumn('user_one_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('user_two_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index([
                        'user_one_id',
                        'user_two_id',
                    ])
                    ->setName('unique_conversation')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('user_two_id')
                    ->setName('user2_id')
            )
            ->create();

        $this->table('favorite_places')
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('address', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('latitude', 'decimal', [
                'default' => null,
                'null' => false,
                'precision' => 10,
                'scale' => 8,
                'signed' => true,
            ])
            ->addColumn('longitude', 'decimal', [
                'default' => null,
                'null' => false,
                'precision' => 10,
                'scale' => 8,
                'signed' => true,
            ])
            ->addColumn('category', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('user_id')
                    ->setName('id_utilisateur')
            )
            ->create();

        $this->table('favorites')
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('roadtrip_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index([
                        'user_id',
                        'roadtrip_id',
                    ])
                    ->setName('unique_favori')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('roadtrip_id')
                    ->setName('id_roadtrip')
            )
            ->addIndex(
                $this->index('user_id')
                    ->setName('idx_favoris_utilisateur')
            )
            ->create();

        $this->table('friends')
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('friend_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('status', 'string', [
                'default' => 'pending',
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('user_id')
                    ->setName('user_id')
            )
            ->addIndex(
                $this->index('friend_id')
                    ->setName('friend_id')
            )
            ->create();

        $this->table('geocoded_places')
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('latitude', 'decimal', [
                'default' => null,
                'null' => false,
                'precision' => 10,
                'scale' => 7,
                'signed' => true,
            ])
            ->addColumn('longitude', 'decimal', [
                'default' => null,
                'null' => false,
                'precision' => 10,
                'scale' => 7,
                'signed' => true,
            ])
            ->addColumn('type', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('name')
                    ->setName('nom')
                    ->setType('unique')
            )
            ->create();

        $this->table('histories')
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('roadtrip_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('user_id')
                    ->setName('id_utilisateur')
            )
            ->addIndex(
                $this->index('roadtrip_id')
                    ->setName('id_roadtrip')
            )
            ->create();

        $this->table('messages')
            ->addColumn('conversation_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => true,
            ])
            ->addColumn('sender_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('recipient_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('body', 'binary', [
                'default' => null,
                'limit' => 4294967295,
                'null' => false,
            ])
            ->addColumn('is_read', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('delivered_at', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('read_at', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('nonce', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addIndex(
                $this->index('sender_id')
                    ->setName('expediteur_id')
            )
            ->addIndex(
                $this->index('conversation_id')
                    ->setName('idx_messages_conversation')
            )
            ->addIndex(
                $this->index([
                        'recipient_id',
                        'is_read',
                    ])
                    ->setName('idx_messages_destinataire')
            )
            ->addIndex(
                $this->index('conversation_id')
                    ->setName('idx_conversation_id')
            )
            ->addIndex(
                $this->index('recipient_id')
                    ->setName('idx_destinataire_id')
            )
            ->create();

        $this->table('notification_preferences')
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('receive_message_notifications', 'boolean', [
                'default' => true,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('receive_email_notifications', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('receive_roadtrip_notifications', 'boolean', [
                'default' => true,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('receive_file_notifications', 'boolean', [
                'default' => true,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('quiet_hours_start', 'time', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('quiet_hours_end', 'time', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('user_id')
                    ->setName('user_id')
                    ->setType('unique')
            )
            ->create();

        $this->table('notifications')
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('type', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('message', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('is_read', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('user_id')
                    ->setName('user_id')
            )
            ->create();

        $this->table('points_of_interests')
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('latitude', 'decimal', [
                'default' => null,
                'null' => true,
                'precision' => 10,
                'scale' => 7,
                'signed' => true,
            ])
            ->addColumn('longitude', 'decimal', [
                'default' => null,
                'null' => true,
                'precision' => 10,
                'scale' => 7,
                'signed' => true,
            ])
            ->addColumn('category', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => true,
            ])
            ->addIndex(
                $this->index('user_id')
                    ->setName('id_createur')
            )
            ->create();

        $this->table('points_of_interests_roadtrips', ['id' => false, 'primary_key' => ['roadtrip_id', 'points_of_interest_id']])
            ->addColumn('roadtrip_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('points_of_interest_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addIndex(
                $this->index('points_of_interest_id')
                    ->setName('id_poi')
            )
            ->create();

        $this->table('reports')
            ->addColumn('reporter_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('content_type', 'string', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('target_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('reason', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('reporter_id')
                    ->setName('reports_fk_reporter')
            )
            ->create();

        $this->table('roadtrips')
            ->addColumn('title', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('visibility', 'string', [
                'default' => 'public',
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('photo_url', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('status', 'string', [
                'default' => 'draft',
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('place', 'string', [
                'default' => 'europe',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index('user_id')
                    ->setName('roadtrips_fk_user')
            )
            ->create();

        $this->table('shared_roadtrips')
            ->addColumn('roadtrip_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('token', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('view_count', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index('roadtrip_id')
                    ->setName('id_roadtrip')
            )
            ->create();

        $this->table('sub_step_photos')
            ->addColumn('sub_step_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('photo', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addIndex(
                $this->index('sub_step_id')
                    ->setName('sous_etape_id')
            )
            ->create();

        $this->table('sub_steps')
            ->addColumn('order_number', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('city', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => 4294967295,
                'null' => true,
            ])
            ->addColumn('trip_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('transport_type', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('duration', 'time', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('avoid_highways', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('avoid_tolls', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('trip_id')
                    ->setName('trajet_id')
            )
            ->create();

        $this->table('trips')
            ->addColumn('order_number', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('title', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('departure', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('arrival', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('transport_mode', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('roadtrip_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('avoid_highways', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('avoid_tolls', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('departure_time', 'time', [
                'default' => '08:00:00',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('date', 'date', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index('roadtrip_id')
                    ->setName('road_trip_id')
            )
            ->create();

        $this->table('user_tokens')
            ->addColumn('selector', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('hashed_validator', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('expires_at', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('selector')
                    ->setName('selector')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('user_id')
                    ->setName('fk_user_tokens_user')
            )
            ->create();

        $this->table('users')
            ->addColumn('last_name', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('first_name', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('email', 'string', [
                'default' => null,
                'limit' => 150,
                'null' => false,
            ])
            ->addColumn('password', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('address', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('zipcode', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => true,
            ])
            ->addColumn('city', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('phone', 'string', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('birth_date', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('profile_picture', 'string', [
                'default' => null,
                'limit' => 500,
                'null' => true,
            ])
            ->addColumn('public_key', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('session_id', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('username', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('reset_token_hash', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('reset_expires_at', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('google_id', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('role', 'string', [
                'default' => 'user',
                'limit' => 20,
                'null' => false,
            ])
            ->addIndex(
                $this->index('email')
                    ->setName('email')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('username')
                    ->setName('pseudo')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('session_id')
                    ->setName('idx_utilisateur_session')
            )
            ->create();

        $this->table('comments')
            ->addForeignKey(
                $this->foreignKey('points_of_interest_id')
                    ->setReferencedTable('points_of_interests')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('RESTRICT')
                    ->setName('comments_ibfk_3')
            )
            ->addForeignKey(
                $this->foreignKey('roadtrip_id')
                    ->setReferencedTable('roadtrips')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('RESTRICT')
                    ->setName('comments_ibfk_2')
            )
            ->addForeignKey(
                $this->foreignKey('user_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('RESTRICT')
                    ->setName('comments_ibfk_1')
            )
            ->update();

        $this->table('conversations')
            ->addForeignKey(
                $this->foreignKey('user_two_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('RESTRICT')
                    ->setName('conversations_ibfk_2')
            )
            ->addForeignKey(
                $this->foreignKey('user_one_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('RESTRICT')
                    ->setName('conversations_ibfk_1')
            )
            ->update();

        $this->table('favorite_places')
            ->addForeignKey(
                $this->foreignKey('user_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('RESTRICT')
                    ->setName('favorite_places_ibfk_1')
            )
            ->update();

        $this->table('favorites')
            ->addForeignKey(
                $this->foreignKey('roadtrip_id')
                    ->setReferencedTable('roadtrips')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('RESTRICT')
                    ->setName('favorites_ibfk_2')
            )
            ->addForeignKey(
                $this->foreignKey('user_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('RESTRICT')
                    ->setName('favorites_ibfk_1')
            )
            ->update();

        $this->table('friends')
            ->addForeignKey(
                $this->foreignKey('friend_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('RESTRICT')
                    ->setName('friends_ibfk_2')
            )
            ->addForeignKey(
                $this->foreignKey('user_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('RESTRICT')
                    ->setName('friends_ibfk_1')
            )
            ->update();

        $this->table('histories')
            ->addForeignKey(
                $this->foreignKey('roadtrip_id')
                    ->setReferencedTable('roadtrips')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('RESTRICT')
                    ->setName('histories_ibfk_2')
            )
            ->addForeignKey(
                $this->foreignKey('user_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('RESTRICT')
                    ->setName('histories_ibfk_1')
            )
            ->update();

        $this->table('messages')
            ->addForeignKey(
                $this->foreignKey('recipient_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('RESTRICT')
                    ->setName('messages_ibfk_3')
            )
            ->addForeignKey(
                $this->foreignKey('sender_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('RESTRICT')
                    ->setName('messages_ibfk_2')
            )
            ->addForeignKey(
                $this->foreignKey('conversation_id')
                    ->setReferencedTable('conversations')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('RESTRICT')
                    ->setName('messages_ibfk_1')
            )
            ->update();

        $this->table('notification_preferences')
            ->addForeignKey(
                $this->foreignKey('user_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('RESTRICT')
                    ->setName('notification_preferences_ibfk_1')
            )
            ->update();

        $this->table('notifications')
            ->addForeignKey(
                $this->foreignKey('user_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setOnDelete('RESTRICT')
                    ->setOnUpdate('RESTRICT')
                    ->setName('notifications_ibfk_1')
            )
            ->update();

        $this->table('points_of_interests')
            ->addForeignKey(
                $this->foreignKey('user_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setOnDelete('SET_NULL')
                    ->setOnUpdate('RESTRICT')
                    ->setName('points_of_interests_ibfk_1')
            )
            ->update();

        $this->table('points_of_interests_roadtrips')
            ->addForeignKey(
                $this->foreignKey('points_of_interest_id')
                    ->setReferencedTable('points_of_interests')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('RESTRICT')
                    ->setName('points_of_interests_roadtrips_ibfk_2')
            )
            ->addForeignKey(
                $this->foreignKey('roadtrip_id')
                    ->setReferencedTable('roadtrips')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('RESTRICT')
                    ->setName('points_of_interests_roadtrips_ibfk_1')
            )
            ->update();

        $this->table('reports')
            ->addForeignKey(
                $this->foreignKey('reporter_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('RESTRICT')
                    ->setName('reports_fk_reporter')
            )
            ->update();

        $this->table('roadtrips')
            ->addForeignKey(
                $this->foreignKey('user_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('RESTRICT')
                    ->setName('roadtrips_fk_user')
            )
            ->update();

        $this->table('shared_roadtrips')
            ->addForeignKey(
                $this->foreignKey('roadtrip_id')
                    ->setReferencedTable('roadtrips')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('RESTRICT')
                    ->setName('shared_roadtrips_ibfk_1')
            )
            ->update();

        $this->table('sub_step_photos')
            ->addForeignKey(
                $this->foreignKey('sub_step_id')
                    ->setReferencedTable('sub_steps')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('RESTRICT')
                    ->setName('sub_step_photos_ibfk_1')
            )
            ->update();

        $this->table('sub_steps')
            ->addForeignKey(
                $this->foreignKey('trip_id')
                    ->setReferencedTable('trips')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('RESTRICT')
                    ->setName('sub_steps_ibfk_1')
            )
            ->update();

        $this->table('trips')
            ->addForeignKey(
                $this->foreignKey('roadtrip_id')
                    ->setReferencedTable('roadtrips')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('RESTRICT')
                    ->setName('trips_ibfk_1')
            )
            ->update();

        $this->table('user_tokens')
            ->addForeignKey(
                $this->foreignKey('user_id')
                    ->setReferencedTable('users')
                    ->setReferencedColumns('id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('RESTRICT')
                    ->setName('fk_user_tokens_user')
            )
            ->update();
    }

    /**
     * Down Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-down-method
     *
     * @return void
     */
    public function down(): void
    {
        $this->table('comments')
            ->dropForeignKey(
                'points_of_interest_id'
            )
            ->dropForeignKey(
                'roadtrip_id'
            )
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('conversations')
            ->dropForeignKey(
                'user_two_id'
            )
            ->dropForeignKey(
                'user_one_id'
            )->save();

        $this->table('favorite_places')
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('favorites')
            ->dropForeignKey(
                'roadtrip_id'
            )
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('friends')
            ->dropForeignKey(
                'friend_id'
            )
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('histories')
            ->dropForeignKey(
                'roadtrip_id'
            )
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('messages')
            ->dropForeignKey(
                'recipient_id'
            )
            ->dropForeignKey(
                'sender_id'
            )
            ->dropForeignKey(
                'conversation_id'
            )->save();

        $this->table('notification_preferences')
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('notifications')
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('points_of_interests')
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('points_of_interests_roadtrips')
            ->dropForeignKey(
                'points_of_interest_id'
            )
            ->dropForeignKey(
                'roadtrip_id'
            )->save();

        $this->table('reports')
            ->dropForeignKey(
                'reporter_id'
            )->save();

        $this->table('roadtrips')
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('shared_roadtrips')
            ->dropForeignKey(
                'roadtrip_id'
            )->save();

        $this->table('sub_step_photos')
            ->dropForeignKey(
                'sub_step_id'
            )->save();

        $this->table('sub_steps')
            ->dropForeignKey(
                'trip_id'
            )->save();

        $this->table('trips')
            ->dropForeignKey(
                'roadtrip_id'
            )->save();

        $this->table('user_tokens')
            ->dropForeignKey(
                'user_id'
            )->save();

        $this->table('comments')->drop()->save();
        $this->table('conversations')->drop()->save();
        $this->table('favorite_places')->drop()->save();
        $this->table('favorites')->drop()->save();
        $this->table('friends')->drop()->save();
        $this->table('geocoded_places')->drop()->save();
        $this->table('histories')->drop()->save();
        $this->table('messages')->drop()->save();
        $this->table('notification_preferences')->drop()->save();
        $this->table('notifications')->drop()->save();
        $this->table('points_of_interests')->drop()->save();
        $this->table('points_of_interests_roadtrips')->drop()->save();
        $this->table('reports')->drop()->save();
        $this->table('roadtrips')->drop()->save();
        $this->table('shared_roadtrips')->drop()->save();
        $this->table('sub_step_photos')->drop()->save();
        $this->table('sub_steps')->drop()->save();
        $this->table('trips')->drop()->save();
        $this->table('user_tokens')->drop()->save();
        $this->table('users')->drop()->save();
    }
}
