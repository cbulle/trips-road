<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * FavoritePlaces Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\FavoritePlace newEmptyEntity()
 * @method \App\Model\Entity\FavoritePlace newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\FavoritePlace> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\FavoritePlace get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\FavoritePlace findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\FavoritePlace patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\FavoritePlace> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\FavoritePlace|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\FavoritePlace saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\FavoritePlace>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\FavoritePlace>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\FavoritePlace>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\FavoritePlace> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\FavoritePlace>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\FavoritePlace>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\FavoritePlace>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\FavoritePlace> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class FavoritePlacesTable extends Table
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

        $this->setTable('favorite_places');
        $this->setDisplayField('name');
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
            ->notEmptyString('user_id');

        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->scalar('address')
            ->maxLength('address', 255)
            ->allowEmptyString('address');

        $validator
            ->decimal('latitude')
            ->requirePresence('latitude', 'create')
            ->notEmptyString('latitude');

        $validator
            ->decimal('longitude')
            ->requirePresence('longitude', 'create')
            ->notEmptyString('longitude');

        $validator
            ->scalar('category')
            ->maxLength('category', 50)
            ->allowEmptyString('category');

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
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }
}
