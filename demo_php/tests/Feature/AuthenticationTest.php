<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Vite;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private string $viteHotFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->viteHotFile = sys_get_temp_dir().'/laravel-vite-hot-'.uniqid();
        file_put_contents($this->viteHotFile, 'http://localhost:5173');
        Vite::useHotFile($this->viteHotFile);
    }

    protected function tearDown(): void
    {
        @unlink($this->viteHotFile);

        parent::tearDown();
    }

    public function test_registration_creates_a_hashed_password_and_authenticates_the_user(): void
    {
        $response = $this->post('/register', [
            'name' => 'Alice Exemple',
            'email' => 'alice@example.com',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
        ]);

                $this->viteHotFile = sys_get_temp_dir().'/laravel-vite-hot-'.uniqid();
                file_put_contents($this->viteHotFile, 'http://localhost:5173');
                Vite::useHotFile($this->viteHotFile);

        $user = User::where('email', 'alice@example.com')->firstOrFail();

        $this->assertNotSame('StrongPass123!', $user->password);
        $this->assertTrue(Hash::check('StrongPass123!', $user->password));
        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
        $this->get('/')->assertOk()->assertSee('Alice Exemple');
    }

    public function test_login_rejects_invalid_credentials_and_accepts_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'member@example.com',
            'password' => 'StrongPass123!',
        ]);

        $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertRedirect('/login')->assertSessionHasErrors('email');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'StrongPass123!',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_authenticated_user_can_log_out_and_loses_access_to_home(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_registration_validates_password_confirmation_and_unique_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $this->post('/register', [
            'name' => 'Another User',
            'email' => 'existing@example.com',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'different-password',
        ])->assertSessionHasErrors(['email', 'password']);
    }

    public function test_seeded_default_account_uses_a_hashed_password(): void
    {
        $this->seed();

        $user = User::where('email', 'admin@example.com')->firstOrFail();

        $this->assertNotSame('ChangeMe123!', $user->password);
        $this->assertTrue(Hash::check('ChangeMe123!', $user->password));

        $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'ChangeMe123!',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    }
}
