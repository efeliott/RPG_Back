<?php

namespace Laravel\Sanctum;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\Contracts\HasAbilities;

class PersonalAccessToken extends Model implements HasAbilities
{
    /**
     * Les attributs qui doivent être castés vers des types natifs.
     *
     * @var array
     */
    protected $casts = [
        'abilities' => 'json',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Les attributs pouvant être assignés en masse.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'token',
        'tokenable_id',
        'tokenable_type',
        'abilities',
        'expires_at',
    ];

    /**
     * Les attributs à cacher lors de la sérialisation.
     *
     * @var array
     */
    protected $hidden = [
        'token',
    ];

    /**
     * Obtenir le modèle associé auquel le token appartient.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function tokenable()
    {
        return $this->morphTo('tokenable');
    }

    /**
     * Rechercher l'instance de token correspondant au token donné.
     *
     * @param  string  $token
     * @return static|null
     */
    public static function findToken($token)
    {
        if (strpos($token, '|') === false) {
            return static::where('token', hash('sha256', $token))->first();
        }

        [$id, $token] = explode('|', $token, 2);

        if ($instance = static::find($id)) {
            return hash_equals($instance->token, hash('sha256', $token)) ? $instance : null;
        }

        return null; // Assure de retourner explicitement `null` si aucune correspondance n'est trouvée.
    }

    /**
     * Vérifier si le token possède une capacité donnée.
     *
     * @param  string  $ability
     * @return bool
     */
    public function can($ability)
    {
        return in_array('*', $this->abilities) ||
               array_key_exists($ability, array_flip($this->abilities));
    }

    /**
     * Vérifier si le token n'a pas une capacité donnée.
     *
     * @param  string  $ability
     * @return bool
     */
    public function cant($ability)
    {
        return ! $this->can($ability);
    }
}