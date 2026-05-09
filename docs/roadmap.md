
## 権限管理 UI 改善

### deny permission の理由表示

`member_permissions.effect = deny` の権限が効いてアクセス拒否された場合、管理画面上で deny の理由を確認できるようにする。

対象情報：

- 対象メンバー
- 対象Permission
- 対象Organization
- deny理由（member_permissions.reason）
- 付与者（granted_by）
- 付与日時（granted_at）
- 失効日時（revoked_at）
- 現在の状態（status）

想定UI：

- メンバー詳細画面に「Direct Permissions」を表示
- allow / deny を区別して表示
- deny は理由を必ず表示
- 権限判定で拒否された場合、管理者向けには拒否理由を確認できる導線を用意する

注意：

- 一般ユーザーに詳細なdeny理由をそのまま表示するかは未定
- 初期実装では管理画面のみ表示対象とする

