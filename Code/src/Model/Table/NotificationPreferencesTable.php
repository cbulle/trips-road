<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * NotificationPreferences Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\NotificationPreference newEmptyEntity()
 * @method \App\Model\Entity\NotificationPreference newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\NotificationPreference> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\NotificationPreference get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\NotificationPreference findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\NotificationPreference patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\NotificationPreference> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\NotificationPreference|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\NotificationPreference saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\NotificationPreference>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\NotificationPreference>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\NotificationPreference>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\NotificationPreference> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\NotificationPreference>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\NotificationPreference>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\NotificationPreference>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\NotificationPreference> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class NotificationPreferencesTable extends Table
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

        $this->setTable('notification_preferences');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
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
            ->integer('user_id')
            ->notEmptyString('user_id')
            ->add('user_id', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->boolean('receive_message_notifications')
            ->allowEmptyString('receive_message_notifications');

        $validator
            ->boolean('receive_email_notifications')
            ->allowEmptyString('receive_email_notifications');

        $validator
            ->boolean('receive_roadtrip_notifications')
            ->allowEmptyString('receive_roadtrip_notifications');

        $validator
            ->boolean('receive_file_notifications')
            ->allowEmptyString('receive_file_notifications');

        $validator
            ->time('quiet_hours_start')
            ->allowEmptyTime('quiet_hours_start');

        $validator
            ->time('quiet_hours_end')
            ->allowEmptyTime('quiet_hours_end');

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
        $rules->add($rules->isUnique(['user_id']), ['errorField' => 'user_id']);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }
}
