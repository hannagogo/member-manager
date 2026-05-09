<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Member;
use App\Models\MemberAccount;
use App\Models\MemberRole;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class MemberManagerSeeder extends Seeder
{
    public function run(): void
    {
        $region = Organization::firstOrCreate(
            ['code' => 'chiba-central-region'],
            [
                'name' => '千葉セントラルリージョン',
                'type' => 'region',
                'status' => 'active',
            ]
        );

        $dna = Organization::firstOrCreate(
            ['code' => 'dna-team'],
            [
                'parent_id' => $region->id,
                'name' => 'DNAチーム',
                'type' => 'team',
                'status' => 'active',
            ]
        );

        $ops = Organization::firstOrCreate(
            ['code' => 'operation-team'],
            [
                'parent_id' => $region->id,
                'name' => 'オペレーションチーム',
                'type' => 'team',
                'status' => 'active',
            ]
        );

        $roles = [
            'managing_director' => [
                'short' => 'MD',
                'ja' => 'マネージングディレクター',
                'en' => 'Managing Director',
            ],
            'senior_director' => [
                'short' => 'SND',
                'ja' => 'シニアディレクター',
                'en' => 'Senior Director',
            ],
            'dna_lt' => [
                'short' => 'DNA LT',
                'ja' => 'DNA LT',
                'en' => 'DNA LT',
            ],
            'editor' => [
                'short' => 'EDITOR',
                'ja' => '編集者',
                'en' => 'Editor',
            ],
            'viewer' => [
                'short' => 'VIEWER',
                'ja' => '閲覧者',
                'en' => 'Viewer',
            ],
        ];

        foreach ($roles as $code => $roleData) {
            Role::firstOrCreate(
                ['code' => $code],
                [
                    'short_name' => $roleData['short'],
                    'name_ja' => $roleData['ja'],
                    'name_en' => $roleData['en'],
                ]
            );
        }

        $permissions = [
            'mediawiki_read' => 'MediaWiki閲覧',
            'mediawiki_edit' => 'MediaWiki編集',
            'docs_read' => 'Google Docs閲覧',
            'docs_edit' => 'Google Docs編集',
            'member_manage' => 'メンバー管理',
            'role_manage' => '役割管理',
        ];

        foreach ($permissions as $code => $name) {
            Permission::firstOrCreate(
                ['code' => $code],
                ['name' => $name]
            );
        }

        $md = Role::where('code', 'managing_director')->first();
        $editor = Role::where('code', 'editor')->first();

        $md->permissions()->syncWithoutDetaching(
            Permission::whereIn('code', [
                'mediawiki_read',
                'mediawiki_edit',
                'docs_read',
                'docs_edit',
                'member_manage',
                'role_manage',
            ])->pluck('id')->all()
        );

        $editor->permissions()->syncWithoutDetaching(
            Permission::whereIn('code', [
                'mediawiki_read',
                'mediawiki_edit',
                'docs_read',
                'docs_edit',
            ])->pluck('id')->all()
        );

        $member = Member::firstOrCreate(
            ['email' => 'omi@period-inc.co.jp'],
            [
                'display_name' => '菊池 正臣',
                'legal_name' => '菊池 正臣',
                'kana_name' => 'キクチ マサオミ',
                'status' => 'active',
                'joined_at' => now(),
            ]
        );

        MemberAccount::firstOrCreate(
            [
                'provider' => 'google',
                'account_identifier' => 'omi@period-inc.co.jp',
            ],
            [
                'member_id' => $member->id,
                'is_primary' => true,
                'status' => 'active',
                'verified_at' => now(),
            ]
        );

        Membership::firstOrCreate(
            [
                'member_id' => $member->id,
                'organization_id' => $dna->id,
            ],
            [
                'status' => 'active',
                'started_at' => now(),
            ]
        );

        MemberRole::firstOrCreate(
            [
                'member_id' => $member->id,
                'role_id' => $md->id,
                'organization_id' => $region->id,
            ],
            [
                'status' => 'active',
                'granted_at' => now(),
            ]
        );

        AuditLog::create([
            'actor_member_id' => $member->id,
            'action' => 'seed.member_manager.initialized',
            'target_type' => 'member',
            'target_id' => $member->id,
            'after_json' => [
                'member' => $member->display_name,
                'organizations' => [$region->code, $dna->code, $ops->code],
            ],
        ]);
    }
}
