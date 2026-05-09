# Coding Rules

## AI向け重要ルール

- 全文貼り替えを避ける
- 差分指示を優先
- 既存実装確認後に追加
- migration 重複禁止
- Seeder 重複禁止
- relation 名重複禁止
- Resolver ロジック集中管理
- Permission 判定は PermissionResolver に集約
- 権限判定を Controller に直接書かない

## 実装優先順位

1. 整合性
2. 履歴保持
3. 可読性
4. 拡張性
5. UI

## UI方針

- UIは最小構成
- 運用後に改善
- 権限モデルを優先

## Token削減方針

- 既存コード全文を毎回貼らない
- 変更対象のみ示す
- docs/ai-context を前提共有に使う
- 固定略称を使用する

## 略称

- PR = PermissionResolver
- MR = MemberRole
- MP = MemberPermission
- ORG = Organization
- OIH = Organization Inheritance
- DP = Direct Permission
