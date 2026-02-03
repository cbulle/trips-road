<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Favorites Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\RoadtripsTable&\Cake\ORM\Association\BelongsTo $Roadtrips
 *
 * @method \App\Model\Entity\Favorite newEmptyEntity()
 * @method \App\Model\Entity\Favorite newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Favorite> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Favorite get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Favorite findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Favorite patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Favorite> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Favorite|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Favorite saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Favorite>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Favorite>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Favorite>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Favorite> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Favorite>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Favorite>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Favorite>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Favorite> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class FavoritesTable extends Table
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

        $this->setTable('favorites');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Roadtrips', [
            'foreignKey' => 'roadtrip_id',
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
            ->integer('roadtrip_id')
            ->notEmptyString('roadtrip_id');

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
        $rules->add($rules->isUnique(['user_id', 'roadtrip_id']), ['errorField' => 'user_id', 'message' => __('This combination of user_id and roadtrip_id already exists')]);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);
        $rules->add($rules->existsIn(['roadtrip_id'], 'Roadtrips'), ['errorField' => 'roadtrip_id']);

        return $rules;
    }
}
