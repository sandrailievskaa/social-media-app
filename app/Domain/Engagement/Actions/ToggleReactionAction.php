<?php

namespace App\Domain\Engagement\Actions;

use App\Domain\Engagement\Enums\ReactionType;
use App\Domain\IdentityAndAccess\Models\User;
use Illuminate\Database\Eloquent\Model;

class ToggleReactionAction
{
    /**
     * @return array<string, int>
     */
    public function execute(User $user, Model $reactable, ReactionType $type): array
    {
        $existing = $reactable->reactions()
            ->where('user_id', $user->getKey())
            ->first();

        if ($existing === null) {
            $reactable->reactions()->create([
                'user_id' => $user->getKey(),
                'type' => $type,
            ]);
        } elseif ($existing->type === $type) {
            $existing->delete();
        } else {
            $existing->update([
                'type' => $type,
            ]);
        }

        return $reactable->reactions()
            ->selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->map(fn ($count) => (int) $count)
            ->toArray();
    }
}
