<?php

namespace App\Services;

use App\Models\Member;
use App\Models\MemberAccount;
use App\Models\MemberRole;
use App\Models\Organization;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DnaMemberImportService
{
    public function import(string $path): void
    {
        $handle = fopen($path, 'r');

        $header = fgetcsv($handle);

        $header = array_map(
            fn ($value) => $this->normalize($value),
            $header
        );

        $headerCount = count($header);

        $rows = [];

        while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {

            $row = array_map(
                fn ($value) => $this->normalize($value),
                $row
            );

            if (count($row) < $headerCount) {
                $row = array_pad($row, $headerCount, null);
            }

            if (count($row) > $headerCount) {
                $row = array_slice($row, 0, $headerCount);
            }

            $rows[] = $row;
        }

        fclose($handle);

        DB::transaction(function () use ($rows, $header, $headerCount) {


            $sponsorRelations = [];

            foreach ($rows as $row) {

                if (count(array_filter($row)) === 0) {
                    continue;
                }

                if (count($row) < $headerCount) {
                    $row = array_pad($row, $headerCount, null);
                }

                if (count($row) > $headerCount) {
                    $row = array_slice($row, 0, $headerCount);
                }

                $row = array_map(
                    fn ($value) => $this->normalize($value),
                    $row
                );

                $data = array_combine($header, $row);

                $memberName = trim((string) ($data['名前'] ?? ''));

                $member = Member::create([
                    'display_name' => $memberName,
                    'legal_name' => $memberName,
                    'status' => 'active',
                    'is_trainer' => ($data['トレーナー'] ?? '') === '○',
                ]);

                $this->importRole($member, $data);
                $this->importOrganizations($member, $data);
                $this->importAccounts($member, $data);

                $sponsorName = trim((string) ($data['スポンサー名'] ?? ''));

                if ($sponsorName !== '') {
                    $sponsorRelations[] = [
                        'member_id' => $member->id,
                        'sponsor_name' => $sponsorName,
                    ];
                }
            }
            foreach ($sponsorRelations as $relation) {

                $sponsor = Member::query()
                    ->where('display_name', $relation['sponsor_name'])
                    ->first();

                if (! $sponsor) {
                    continue;
                }

                Member::query()
                    ->where('id', $relation['member_id'])
                    ->update([
                        'sponsor_member_id' => $sponsor->id,
                    ]);
            }

        });
    }


    private function normalize(?string $value): string
    {
        $value = (string) $value;

        $value = mb_convert_encoding(
            $value,
            'UTF-8',
            'UTF-8, SJIS-win, CP932, UTF-16, UTF-16LE, UTF-16BE'
        );

        $value = preg_replace('/\x00/u', '', $value);

        $value = preg_replace('/[[:cntrl:]]/u', '', $value);

        return trim($value);
    }

    private function importRole(Member $member, array $data): void
    {
        $abbr = trim($data['DNA役職'] ?? '');

        if (! $abbr) {
            return;
        }

        $map = [
            'ED' => [
                'code' => 'executive_director',
                'name_ja' => 'エグゼクティブディレクター',
                'name_en' => 'Executive Director',
            ],
            'MD' => [
                'code' => 'managing_director',
                'name_ja' => 'マネージングディレクター',
                'name_en' => 'Managing Director',
            ],
            'SND' => [
                'code' => 'senior_network_director',
                'name_ja' => 'シニアネットワークディレクター',
                'name_en' => 'Senior Network Director',
            ],
            'OC' => [
                'code' => 'operation_consultant',
                'name_ja' => 'オペレーションコンサルタント',
                'name_en' => 'Operation Consultant',
            ],
            'DC' => [
                'code' => 'director_consultant',
                'name_ja' => 'ディレクターコンサルタント',
                'name_en' => 'Director Consultant',
            ],
            'A' => [
                'code' => 'ambassador',
                'name_ja' => 'アンバサダー',
                'name_en' => 'Ambassador',
            ],
            'SD' => [
                'code' => 'support_director',
                'name_ja' => 'サポートディレクター',
                'name_en' => 'Support Director',
            ],
            'AS' => [
                'code' => 'ambassador_support',
                'name_ja' => 'アンバサダーサポート',
                'name_en' => 'Ambassador Support',
            ],
            'OD' => [
                'code' => 'operation_director',
                'name_ja' => 'オペレーションディレクター',
                'name_en' => 'Operation Director',
            ],
            'OA' => [
                'code' => 'operation_ambassador',
                'name_ja' => 'オペレーションアンバサダー',
                'name_en' => 'Operation Ambassador',
            ],
        ];

        if (! isset($map[$abbr])) {
            return;
        }

        $roleData = $map[$abbr];

        $role = Role::firstOrCreate(
            ['code' => $roleData['code']],
            [
                'abbr' => $abbr,
                'name_ja' => $roleData['name_ja'],
                'name_en' => $roleData['name_en'],
            ]
        );

        MemberRole::create([
            'member_id' => $member->id,
            'role_id' => $role->id,
            'status' => 'active',
        ]);
    }

    private function importOrganizations(Member $member, array $data): void
    {
        $fields = [
            '所属チャプター' => 'chapter',
            '担当チャプター' => 'assigned_chapter',
            'ローンチチーム' => 'launch_team',
            'オペレーションチーム' => 'operation_team',
        ];

        foreach ($fields as $column => $type) {

            $name = trim($data[$column] ?? '');

            if (! $name) {
                continue;
            }

            $organization = Organization::firstOrCreate(
                [
                    'type' => $type,
                    'code' => md5($type . ':' . $name),
                ],
                [
                    'name' => $name,
                ]
            );

            $member->memberships()->create([
                'organization_id' => $organization->id,
                'status' => 'active',
            ]);
        }
    }

    private function importAccounts(Member $member, array $data): void
    {
        $accounts = [
            'google_sheet' => $data['スプレッドシート登録用Emailアドレス'] ?? null,
            'bni_connect' => $data['コネクト登録アドレス'] ?? null,
        ];

        foreach ($accounts as $provider => $email) {

            $email = trim((string) $email);

            if (! $email) {
                continue;
            }

            MemberAccount::create([
                'member_id' => $member->id,
                'provider' => $provider,
                'account_identifier' => $email,
                'status' => 'active',
            ]);
        }
    }
}
