<?php

namespace Src\Games\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Src\Games\Application\DTOs\DTOSecretSantaDrawRequest;
use Src\Games\Application\Services\GamesService;
use Src\Games\Domain\Exceptions\SecretSantaAlreadyViewedException;
use Src\Games\Domain\Exceptions\SecretSantaException;
use Src\Games\Domain\Exceptions\SecretSantaNotFoundException;
use Src\Games\Infrastructure\Http\Requests\SecretSantaDrawRequest;

class GamesController extends Controller
{
    public function __construct(private GamesService $service) {}

    public function secretSanta(): View
    {
        return view('Games.secret-santa', [
            'campaigns' => $this->service->campaigns(),
        ]);
    }

    public function campaignMembers(string $uuid): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->service->campaignMembers($uuid),
        ]);
    }

    /** Solo admin: último sorteo guardado de la campaña con estado visto/pendiente. */
    public function latestDraw(string $uuid): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->formatDraw($this->service->latestDraw($uuid)),
        ]);
    }

    /** Solo admin: exclusiones guardadas de la campaña. */
    public function exclusions(string $uuid): JsonResponse
    {
        return $this->exclusionsResponse(fn () => $this->service->exclusions($uuid));
    }

    public function addExclusion(Request $request, string $uuid): JsonResponse
    {
        $data = $request->validate([
            'giver'    => ['required', 'integer'],
            'receiver' => ['required', 'integer'],
            'both'     => ['sometimes', 'boolean'],
        ]);

        return $this->exclusionsResponse(fn () => $this->service->addExclusion(
            $uuid, (int) $data['giver'], (int) $data['receiver'], (bool) ($data['both'] ?? false), $request->user()?->oid,
        ));
    }

    public function removeExclusion(Request $request, string $uuid): JsonResponse
    {
        $data = $request->validate([
            'giver'    => ['required', 'integer'],
            'receiver' => ['required', 'integer'],
        ]);

        return $this->exclusionsResponse(fn () => $this->service->removeExclusion($uuid, (int) $data['giver'], (int) $data['receiver']));
    }

    private function exclusionsResponse(callable $fn): JsonResponse
    {
        try {
            return response()->json(['success' => true, 'data' => $fn()]);
        } catch (SecretSantaException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    /** Solo admin: historial de sorteos realizados. */
    public function history(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->service->drawHistory()]);
    }

    /** Solo admin: detalle de un sorteo del historial. */
    public function showDraw(string $uuid): JsonResponse
    {
        $draw = Str::isUuid($uuid) ? $this->service->draw($uuid) : null;

        if ($draw === null) {
            return response()->json(['success' => false, 'error' => 'Sorteo no encontrado.'], 404);
        }

        return response()->json(['success' => true, 'data' => $this->formatDraw($draw)]);
    }

    public function drawSecretSanta(SecretSantaDrawRequest $request): JsonResponse
    {
        try {
            $draw = $this->service->drawSecretSanta(DTOSecretSantaDrawRequest::fromRequest($request));

            return response()->json(['success' => true, 'data' => $this->formatDraw($draw)]);
        } catch (SecretSantaException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    /** Página pública del enlace personal. */
    public function revealPage(string $token): View
    {
        $preview = $this->service->assignmentPreview($token);

        return view('Games.secret-santa-reveal', [
            'token'   => $token,
            'preview' => $preview,
        ]);
    }

    public function reveal(string $token): JsonResponse
    {
        try {
            return response()->json(['success' => true, 'data' => $this->service->revealSecretSanta($token)]);
        } catch (SecretSantaNotFoundException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 404);
        } catch (SecretSantaAlreadyViewedException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage(), 'viewed' => true], 409);
        }
    }

    /** Solo admin: permite volver a ver el enlace. */
    public function resetView(string $token): JsonResponse
    {
        try {
            $this->service->resetSecretSantaView($token);

            return response()->json(['success' => true]);
        } catch (SecretSantaNotFoundException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 404);
        }
    }

    private function formatDraw(?array $draw): ?array
    {
        if ($draw === null) {
            return null;
        }

        $draw['assignments'] = array_map(fn ($a) => [
            'giver_name' => $a['giver_name'],
            'token'      => $a['token'],
            'url'        => route('games.secret-santa.reveal-page', $a['token']),
            'viewed'     => $a['viewed_at'] !== null,
        ], $draw['assignments']);

        return $draw;
    }
}
