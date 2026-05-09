# Architecture

## Core Models

- User
- Member
- Organization
- Membership
- Role
- Permission
- MemberRole
- MemberPermission
- MemberAccount

## Permission Resolution

PermissionResolver がシステム全体の権限解決を担う。

### 対応範囲

- Role Permission
- Direct Permission
- Scoped Permission
- Inherited Permission
- deny Permission
- revoked_at
- status

## Organization Hierarchy

親Organizationの権限は子Organizationへ継承される。

例：

Region
└ Chapter
  └ DNA Team

## Identity Model

1 Member は複数 OAuth Account を保持可能。

例：

Member
├ Google Workspace
├ 個人Gmail
└ 将来的には Discord / Slack 等

## Auditability

退会後も履歴整合性を保持する。

- 編集履歴
- 権限履歴
- Organization履歴
- OAuth履歴
