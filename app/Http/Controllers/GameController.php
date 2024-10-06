<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    /**
     * Create a new game
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'player1' => 'required|string',
        ]);

        $game = Game::create([
            'player1' => $validated['player1'],
            'board' => array_fill(0, 9, ''),
        ]);

        return response()->json($game, 201);
    }

    /**
     * Join existing game
     */
    public function join(Request $request, $gameId)
    {
        // Validate the incoming request to ensure 'player2' is provided as a non-empty string
        $validated = $request->validate([
            'player2' => 'required|string',
        ]);

        $game = Game::findOrFail($gameId);

        // Assign the validated 'player2' name to the game instance
        $game->player2 = $validated['player2'];

        $game->save();

        return response()->json($game);
    }

    /**
     * Make a move on the board
     */
    public function makeMove(Request $request, $gameId)
    {
        // Validate the incoming request to ensure 'position' is an integer between 0 and 8, 
        // and 'player' is either 'X' or 'O'
        $validated = $request->validate([
            'position' => 'required|integer|min:0|max:8',
            'player' => 'required|string|in:X,O',
        ]);

        $game = Game::findOrFail($gameId);

        // Check if it's the correct player's turn
        if ($game->current_turn !== $validated['player']) {
            return response()->json(['message' => 'Not your turn.'], 400);
        }

        // Check if the move is valid
        if ($game->board[$validated['position']] !== '') {
            return response()->json(['message' => 'Invalid move.'], 400);
        }

        // Update the game board with the player's move
        $board = $game->board;
        $board[$validated['position']] = $validated['player'];

        $game->board = $board;

        $game->current_turn = $game->current_turn === 'X' ? 'O' : 'X'; // Switch turn
        $game->save();

        // Check for win/draw conditions after the move
        $winner = $this->checkWin($game->board);
        if ($winner) {
            $game->status = 'finished';
            $game->winner = $winner;
            $game->save();
        }

        return response()->json($game);
    }

    /**
     * Get an existing game
     */
    public function show($gameId)
    {
        $game = Game::findOrFail($gameId);
        return response()->json($game);
    }

    /**
     * Check if win/draw condition has been met
     */
    private function checkWin($board)
    {
        $winningCombinations = [
            [0, 1, 2], // Horizontal
            [3, 4, 5],
            [6, 7, 8],
            [0, 3, 6], // Vertical
            [1, 4, 7],
            [2, 5, 8],
            [0, 4, 8], // Diagonal
            [2, 4, 6],
        ];

        foreach ($winningCombinations as $combination) {
            if (
                $board[$combination[0]] !== '' &&
                $board[$combination[0]] === $board[$combination[1]] &&
                $board[$combination[0]] === $board[$combination[2]]
            ) {
                return $board[$combination[0]]; // Return winner
            }
        }

        // Check for draw
        if (!in_array('', $board)) {
            return 'draw';
        }

        return null; // No winner yet
    }
}
