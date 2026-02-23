<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Messages Model - Simplifié
 * Sans les associations inutiles
 */
class MessagesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('messages');
        $this->setEntityClass(\App\Model\Entity\Message::class);
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Conversations', [
            'foreignKey' => 'conversation_id',
            'joinType' => 'INNER',
        ]);

        $this->belongsTo('Senders', [
            'foreignKey' => 'sender_id',
            'className' => 'Users',
            'joinType' => 'INNER',
        ]);

        $this->belongsTo('Recipients', [
            'foreignKey' => 'recipient_id',
            'className' => 'Users',
            'joinType' => 'INNER',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('conversation_id')
            ->notEmptyString('conversation_id');

        $validator
            ->integer('sender_id')
            ->notEmptyString('sender_id');

        $validator
            ->integer('recipient_id')
            ->notEmptyString('recipient_id');

        $validator
            ->maxLength('body', 4294967295)
            ->requirePresence('body', 'create')
            ->allowEmptyString('body') ;

        $validator
            ->boolean('is_read')
            ->allowEmptyString('is_read');

        $validator
            ->scalar('nonce')
            ->maxLength('nonce', 50)
            ->allowEmptyString('nonce');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['conversation_id'], 'Conversations'));
        $rules->add($rules->existsIn(['sender_id'], 'Senders'));
        $rules->add($rules->existsIn(['recipient_id'], 'Recipients'));

        return $rules;
    }
}
