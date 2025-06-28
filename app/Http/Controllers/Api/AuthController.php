namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $credentials = $request->validated();

            if (!Auth::attempt($credentials)) {
                return $this->unauthorizedResponse('Invalid credentials');
            }

            $user = Auth::user();

            if ($user->status !== 'active') {
                return $this->forbiddenResponse('Account is inactive');
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->successResponse([
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
            ], 'Login successful');

        } catch (\Exception $e) {
            return $this->errorResponse('Login failed: ' . $e->getMessage(), 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return $this->successResponse(null, 'Logout successful');
        } catch (\Exception $e) {
            return $this->errorResponse('Logout failed: ' . $e->getMessage(), 500);
        }
    }

    public function user(Request $request): JsonResponse
    {
        try {
            return $this->successResponse(
                $request->user()->load('tasks'),
                'User data retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve user data: ' . $e->getMessage(), 500);
        }
    }
}
