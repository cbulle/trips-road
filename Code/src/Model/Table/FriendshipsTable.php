<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Friendships Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\FriendshipsTable&\Cake\ORM\Association\BelongsTo $Friendships
 *
 * @method \App\Model\Entity\Friend newEmptyEntity()
 * @method \App\Model\Entity\Friend newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Friend> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Friend get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Friend findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Friend patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Friend> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Friend|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Friend saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Friend>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Friend>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Friend>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Friend> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Friend>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Friend>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Friend>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Friend> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class FriendshipsTable extends Table
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

        $this->setTable('friends');

        // Clé primaire composite
        $this->setPrimaryKey(['user_id', 'friend_id']);

        $this->addBehavior('Timestamp');

        // Utilisateur source
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);

        $this->belongsTo('FriendsUsers', [
            'className' => 'Users',
            'foreignKey' => 'friend_id',
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

            ->integer('friend_id')
            ->notEmptyString('friend_id')
            ->add('friend_id', 'differentUser', [
                'rule' => function ($value, $context) {
                    return $value !== ($context['data']['user_id'] ?? null);
                },
                'message' => 'Vous ne pouvez pas vous ajouter vous-même',
            ]);

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */

}
