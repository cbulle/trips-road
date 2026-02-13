<?php
namespace App\Model\Table;

use Cake\ORM\Table;

class HistoriqueTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        // 1. LA LIGNE IMPORTANTE : On force le nom de la table
        $this->setTable('histories'); 

        // 2. Clé primaire (généralement 'id')
        $this->setPrimaryKey('id');

        // 3. Gestion automatique des dates (created/modified)
        $this->addBehavior('Timestamp');

        // 4. Les relations
        $this->belongsTo('Roadtrips', [
            'foreignKey' => 'roadtrip_id',
            'joinType' => 'INNER'
        ]);

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id'
        ]);
    }
}