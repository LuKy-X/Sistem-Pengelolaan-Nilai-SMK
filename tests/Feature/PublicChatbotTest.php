<?php

namespace Tests\Feature;

use App\Ai\Agents\SchoolAssistant;
use App\Enums\CareerOpportunityStatus;
use App\Enums\CareerOpportunityType;
use App\Enums\ContentStatus;
use App\Models\AcademicYear;
use App\Models\AdmissionPath;
use App\Models\AdmissionPeriod;
use App\Models\AdmissionRequirement;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\CareerCompany;
use App\Models\CareerOpportunity;
use App\Models\Department;
use App\Models\ProductCategory;
use App\Models\SchoolProfile;
use App\Models\SiteStatistic;
use App\Models\StudentProduct;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SchoolProfileSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Laravel\Ai\Ai;
use Laravel\Ai\Contracts\ConversationStore;
use Tests\TestCase;

class PublicChatbotTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = '/tanya-ai';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SchoolProfileSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(DepartmentSeeder::class);
        $this->seed(UserSeeder::class);
    }

    public function test_opening_greeting_is_school_specific_and_offers_choices(): void
    {
        $profile = SchoolProfile::firstOrFail();

        $response = $this->get(self::ENDPOINT)->assertOk();

        $this->assertStringContainsString($profile->school_name, $response->json('reply'));
        $this->assertNotEmpty($response->json('suggestions'));
    }

    public function test_department_question_lists_every_active_department(): void
    {
        $inactive = Department::factory()->create([
            'code' => 'ZZZ',
            'name' => 'Jurusan Tidak Aktif',
            'is_active' => false,
        ]);

        $reply = $this->ask('Jurusan apa saja yang tersedia?');

        $this->assertSame('department', $reply['intent']);
        $this->assertStringContainsString('Rekayasa Perangkat Lunak', $reply['reply']);
        $this->assertStringContainsString('Tekstil', $reply['reply']);
        $this->assertStringNotContainsString($inactive->name, $reply['reply']);
    }

    /**
     * Visitors type plain Indonesian, not query syntax, so the roots have to
     * resolve even with the usual affixes and typos attached.
     */
    public function test_ordinary_indonesian_phrasing_resolves_to_the_right_intent(): void
    {
        $this->assertSame('department', $this->ask('jurusannya ada apa aja')['intent']);
        $this->assertSame('department', $this->ask('kompetensi ada apa aja')['intent']);
        $this->assertSame('department', $this->ask('Tentang RPL dong')['intent']);
        $this->assertSame('admission', $this->ask('ppdb nya gimana')['intent']);
        $this->assertSame('product', $this->ask('daftar produk ada apa saja')['intent']);
        $this->assertSame('career', $this->ask('ada lowongan kerja sekarang?')['intent']);
        $this->assertSame('news', $this->ask('kabar terbaru sekolah?')['intent']);
        $this->assertSame('contact', $this->ask('alamat sekolahnya dimana')['intent']);
    }

    /**
     * A bare topic word should stay short; asking for detail is what earns the
     * full breakdown instead of repeating the same wall of text.
     */
    public function test_detail_level_changes_the_admission_answer_length(): void
    {
        $academicYear = AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);

        $period = AdmissionPeriod::create([
            'academic_year_id' => $academicYear->id,
            'title' => 'PPDB Gelombang Satu',
            'registration_start' => '2026-06-01',
            'registration_end' => '2026-07-15',
            'status' => 'OPEN',
        ]);

        AdmissionRequirement::create([
            'admission_period_id' => $period->id,
            'title' => 'Fotokopi Ijazah atau Surat Keterangan Lulus',
            'sort_order' => 1,
        ]);

        $summary = $this->ask('ppdb');
        $detailed = $this->ask('syarat ppdb lengkap');

        $this->assertStringNotContainsString('Fotokopi Ijazah', $summary['reply']);
        $this->assertStringContainsString('Fotokopi Ijazah', $detailed['reply']);
        $this->assertGreaterThan(mb_strlen($summary['reply']), mb_strlen($detailed['reply']));
    }

    public function test_naming_a_department_returns_its_competencies_and_a_detail_link(): void
    {
        $department = Department::where('code', 'TKL')->firstOrFail();
        $department->competencies()->create([
            'title' => 'Bubut dan Frais CNC',
            'sort_order' => 1,
        ]);

        $reply = $this->ask('Jurusan TPM punya kompetensi apa saja?');

        $this->assertSame('department', $reply['intent']);
        $this->assertStringContainsString('Bubut dan Frais CNC', $reply['reply']);
        $this->assertStringContainsString(
            route('public.departments.show', $department->code),
            $this->linkUrls($reply),
        );
    }

    public function test_admission_question_reports_the_open_period_dates_and_paths(): void
    {
        $academicYear = AcademicYear::create([
            'name' => '2027/2028',
            'start_date' => '2027-07-01',
            'end_date' => '2028-06-30',
            'is_active' => true,
        ]);

        $period = AdmissionPeriod::create([
            'academic_year_id' => $academicYear->id,
            'title' => 'PPDB Gelombang Satu',
            'registration_start' => '2026-11-01',
            'registration_end' => '2026-12-20',
            'status' => 'OPEN',
        ]);

        AdmissionPath::create([
            'admission_period_id' => $period->id,
            'name' => 'Jalur Prestasi',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $reply = $this->ask('Kapan PPDB dibuka dan syaratnya apa?');

        $this->assertSame('admission', $reply['intent']);
        $this->assertStringContainsString('PPDB Gelombang Satu', $reply['reply']);
        $this->assertStringContainsString('1 November 2026', $reply['reply']);
        $this->assertStringContainsString('20 Desember 2026', $reply['reply']);
        $this->assertStringContainsString('sedang dibuka', $reply['reply']);
        $this->assertStringContainsString('Jalur Prestasi', $reply['reply']);
    }

    public function test_career_question_lists_only_open_opportunities(): void
    {
        $company = CareerCompany::create(['name' => 'PT Contoh Tekstil']);

        CareerOpportunity::create([
            'company_id' => $company->id,
            'type' => CareerOpportunityType::Internship,
            'title' => 'Magang Operator Mesin',
            'location' => 'Sukoharjo',
            'status' => CareerOpportunityStatus::Open,
        ]);

        CareerOpportunity::create([
            'company_id' => $company->id,
            'type' => CareerOpportunityType::Job,
            'title' => 'Lowongan Sudah Ditutup',
            'status' => CareerOpportunityStatus::Closed,
        ]);

        $reply = $this->ask('Ada lowongan PKL sekarang?');

        $this->assertSame('career', $reply['intent']);
        $this->assertStringContainsString('Magang Operator Mesin', $reply['reply']);
        $this->assertStringContainsString('PT Contoh Tekstil', $reply['reply']);
        $this->assertStringNotContainsString('Lowongan Sudah Ditutup', $reply['reply']);
    }

    public function test_product_question_reports_the_price_of_available_products_only(): void
    {
        $category = ProductCategory::create(['name' => 'Busana']);

        StudentProduct::create([
            'category_id' => $category->id,
            'name' => 'Kemeja Seragam Slim Fit',
            'slug' => 'kemeja-seragam-slim-fit',
            'price' => 145000,
            'status' => 'AVAILABLE',
        ]);

        StudentProduct::create([
            'category_id' => $category->id,
            'name' => 'Produk Sudah Terjual',
            'slug' => 'produk-sudah-terjual',
            'price' => 99000,
            'status' => 'SOLD',
        ]);

        $reply = $this->ask('Berapa harga produk karya siswa?');

        $this->assertSame('product', $reply['intent']);
        $this->assertStringContainsString('Kemeja Seragam Slim Fit', $reply['reply']);
        $this->assertStringContainsString('Rp 145.000', $reply['reply']);
        $this->assertStringNotContainsString('Produk Sudah Terjual', $reply['reply']);
    }

    public function test_news_question_lists_only_published_articles(): void
    {
        $author = User::where('username', 'guru.agus')->firstOrFail();
        $category = ArticleCategory::create(['name' => 'Umum', 'slug' => 'umum']);

        Article::create([
            'category_id' => $category->id,
            'author_id' => $author->id,
            'title' => 'Pendaftaran PPDB Resmi Dibuka',
            'slug' => 'ppdb-resmi-dibuka',
            'content' => 'Isi artikel.',
            'status' => ContentStatus::Published,
            'published_at' => now(),
        ]);

        Article::create([
            'category_id' => $category->id,
            'author_id' => $author->id,
            'title' => 'Rencana Invasion Armada',
            'slug' => 'rencana-belum-terbit',
            'content' => 'Isi artikel.',
            'status' => ContentStatus::Draft,
            'published_at' => null,
        ]);

        $reply = $this->ask('Ada berita terbaru?');

        $this->assertSame('news', $reply['intent']);
        $this->assertStringContainsString('Pendaftaran PPDB Resmi Dibuka', $reply['reply']);
        $this->assertStringNotContainsString('Rencana Invasion Armada', $reply['reply']);
    }

    public function test_statistics_question_reads_the_active_site_statistics(): void
    {
        SiteStatistic::create([
            'section' => 'HERO',
            'key' => 'program_keahlian',
            'label' => 'Program Keahlian',
            'value' => '99',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        SiteStatistic::create([
            'section' => 'HERO',
            'key' => 'statistik_disembunyikan',
            'label' => 'Angka Internal',
            'value' => '999',
            'sort_order' => 2,
            'is_active' => false,
        ]);

        $activeDepartments = Department::query()->where('is_active', true)->count();

        $reply = $this->ask('Berapa jumlah siswa aktif sekolah ini?');

        $this->assertSame('statistics', $reply['intent']);
        $this->assertStringContainsString('Program Keahlian', $reply['reply']);
        // The stored "99" is never quoted: the answer counts the department table.
        $this->assertStringContainsString((string) $activeDepartments, $reply['reply']);
        $this->assertStringNotContainsString('99', $reply['reply']);
        $this->assertStringNotContainsString('Angka Internal', $reply['reply']);
    }

    public function test_contact_question_reads_the_school_profile(): void
    {
        $profile = SchoolProfile::firstOrFail();

        $reply = $this->ask('Alamat dan nomor telepon sekolah?');

        $this->assertSame('contact', $reply['intent']);
        $this->assertStringContainsString($profile->address, $reply['reply']);
        $this->assertStringContainsString($profile->phone, $reply['reply']);
    }

    public function test_greeting_and_thanks_short_circuit_before_a_data_lookup(): void
    {
        $this->assertSame('greeting', $this->ask('Halo, apa kabar?')['intent']);
        $this->assertSame('thanks', $this->ask('Terima kasih banyak ya')['intent']);
    }

    public function test_known_local_question_does_not_prompt_the_ai_agent(): void
    {
        SchoolAssistant::fake()->preventStrayPrompts();

        $reply = $this->ask('Halo, apa kabar?');

        $this->assertSame('greeting', $reply['intent']);
    }

    public function test_general_knowledge_question_uses_the_ai_agent(): void
    {
        $this->mockConversationPersistence();
        SchoolAssistant::fake(['Gravitasi adalah gaya tarik-menarik antara benda yang memiliki massa.'])
            ->preventStrayPrompts();

        $question = 'Apa hubungan antara gravitasi dan ruang-waktu?';

        $response = $this->postJson(self::ENDPOINT, [
            'message' => $question,
        ])->assertOk();

        $response->assertJsonPath('intent', 'ai')
            ->assertJsonPath('reply', 'Gravitasi adalah gaya tarik-menarik antara benda yang memiliki massa.');
        Ai::assertAgentWasPrompted(SchoolAssistant::class, $question);
    }

    public function test_unintelligible_message_is_sent_to_ai_for_clarification(): void
    {
        $this->mockConversationPersistence();
        SchoolAssistant::fake(['Maaf, saya belum memahami pesan itu. Bisa kirim ulang pertanyaanmu?'])
            ->preventStrayPrompts();

        $question = 'ajsdjhahdhqiwi0qra';

        $response = $this->postJson(self::ENDPOINT, ['message' => $question])->assertOk();

        $response->assertJsonPath('intent', 'ai')
            ->assertJsonPath('reply', 'Maaf, saya belum memahami pesan itu. Bisa kirim ulang pertanyaanmu?');
        Ai::assertAgentWasPrompted(SchoolAssistant::class, $question);
    }

    public function test_personal_home_address_question_is_refused_without_calling_ai(): void
    {
        SchoolAssistant::fake()->preventStrayPrompts();

        $reply = $this->ask('Kepala sekolah rumahnya mana?');

        $this->assertSame('privacy', $reply['intent']);
        $this->assertStringContainsString('informasi pribadi', $reply['reply']);
        $this->assertStringNotContainsString('Telepon:', $reply['reply']);
    }

    public function test_question_about_unsupported_extracurricular_data_uses_ai(): void
    {
        $this->mockConversationPersistence();
        SchoolAssistant::fake(['Data ekstrakurikuler belum tersedia di sistem sekolah.'])
            ->preventStrayPrompts();

        $response = $this->postJson(self::ENDPOINT, [
            'message' => 'Ada ekstrakulikuler apa saja di sekolah ini?',
        ])->assertOk();

        $response->assertJsonPath('intent', 'ai')
            ->assertJsonPath('reply', 'Data ekstrakurikuler belum tersedia di sistem sekolah.')
            ->assertJsonPath('suggestions', [
                'Jurusan apa saja?',
                'Berapa jumlah siswa per jurusan?',
                'Siapa guru pengampu di tiap kelas?',
            ]);
        Ai::assertAgentWasPrompted(SchoolAssistant::class, 'Ada ekstrakulikuler apa saja di sekolah ini?');
    }

    public function test_question_spanning_student_statistics_and_teaching_assignments_uses_ai(): void
    {
        $this->mockConversationPersistence();
        SchoolAssistant::fake(['Jawaban gabungan berdasarkan data sekolah.'])
            ->preventStrayPrompts();

        $question = 'jumlah semua siswa dan per jurusan serta guru pengampu per kelas';

        $response = $this->postJson(self::ENDPOINT, ['message' => $question])->assertOk();

        $response->assertJsonPath('intent', 'ai')
            ->assertJsonPath('reply', 'Jawaban gabungan berdasarkan data sekolah.');
        Ai::assertAgentWasPrompted(SchoolAssistant::class, $question);
    }

    public function test_class_filtered_student_count_uses_ai_instead_of_site_statistics(): void
    {
        $this->mockConversationPersistence();
        SchoolAssistant::fake(['Jumlah siswa kelas XII RPL A berdasarkan data sistem adalah 28.'])
            ->preventStrayPrompts();

        $question = 'Berapa jumlah siswa di kelas XII RPL A?';

        $response = $this->postJson(self::ENDPOINT, ['message' => $question])->assertOk();

        $response->assertJsonPath('intent', 'ai')
            ->assertJsonPath('reply', 'Jumlah siswa kelas XII RPL A berdasarkan data sistem adalah 28.');
        Ai::assertAgentWasPrompted(SchoolAssistant::class, $question);
    }

    public function test_reply_payload_never_exceeds_the_widget_capacity(): void
    {
        $reply = $this->ask('Jurusan apa saja?');

        $this->assertLessThanOrEqual(3, count($reply['suggestions']));
        $this->assertLessThanOrEqual(2, count($reply['links']));
    }

    public function test_blank_message_is_rejected_with_a_readable_message(): void
    {
        $this->postJson(self::ENDPOINT, ['message' => '   '])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('message')
            ->assertJsonPath('errors.message.0', 'Pertanyaanmu belum diisi.');
    }

    public function test_too_short_message_is_rejected(): void
    {
        $this->postJson(self::ENDPOINT, ['message' => 'a'])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('message')
            ->assertJsonPath(
                'errors.message.0',
                fn (string $message): bool => str_contains($message, 'minimal 2 karakter'),
            );
    }

    public function test_missing_message_is_rejected(): void
    {
        $this->postJson(self::ENDPOINT, [])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('message');
    }

    /**
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}
     */
    private function ask(string $message): array
    {
        /** @var TestResponse $response */
        $response = $this->postJson(self::ENDPOINT, ['message' => $message])->assertOk();

        /** @var array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>} $payload */
        $payload = $response->json();

        return $payload;
    }

    /**
     * @param  array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>}  $payload
     */
    private function linkUrls(array $payload): string
    {
        return implode(' ', array_column($payload['links'], 'url'));
    }

    private function mockConversationPersistence(): void
    {
        $store = $this->mock(ConversationStore::class);
        $store->shouldReceive('storeConversation')->once()->andReturn('01920000-0000-7000-8000-000000000001');
        $store->shouldReceive('storeUserMessage')->once()->andReturn('01920000-0000-7000-8000-000000000002');
        $store->shouldReceive('storeAssistantMessage')->once()->andReturn('01920000-0000-7000-8000-000000000003');
    }
}
