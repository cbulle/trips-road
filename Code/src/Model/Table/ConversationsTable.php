<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Conversations Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $UserOnes
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $UserTwos
 * @property \App\Model\Table\MessagesTable&\Cake\ORM\Association\HasMany $Messages
 *
 * @method \App\Model\Entity\Conversation newEmptyEntity()
 * @method \App\Model\Entity\Conversation newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Conversation> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Conversation get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Conversation findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Conversation patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Conversation> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Conversation|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Conversation saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Conversation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Conversation>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Conversation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Conversation> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Conversation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Conversation>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Conversation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Conversation> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ConversationsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('conversations');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('UserOnes', [
            'foreignKey' => 'user_one_id',
            'className' => 'Users',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('UserTwos', [
            'foreignKey' => 'user_two_id',
            'className' => 'Users',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('Messages', [
            'foreignKey' => 'conversation_id',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('user_one_id')
            ->notEmptyString('user_one_id');

        $validator
            ->integer('user_two_id')
            ->notEmptyString('user_two_id');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['user_one_id', 'user_two_id']), ['errorField' => 'user_one_id', 'message' => __('This combination of user_one_id and user_two_id already exists')]);
        $rules->add($rules->existsIn(['user_one_id'], 'UserOnes'), ['errorField' => 'user_one_id']);
        $rules->add($rules->existsIn(['user_two_id'], 'UserTwos'), ['errorField' => 'user_two_id']);

        return $rules;
    }
}
