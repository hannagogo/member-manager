# Overview

Member Manager は Community Identity Platform を目指す Laravel ベースシステム。

現在の主目的：

- BNI DNAチーム運営
- MediaWiki 権限連携
- Google Workspace 権限連携
- 組織・役割・権限管理

## 現在の設計方針

- RBAC ベース
- Organization Scope 対応
- Permission Inheritance 対応
- Direct Permission 対応
- deny Permission 対応
- 履歴保持重視
- 退会後も履歴保持

## 優先順位

1. 権限整合性
2. 履歴整合性
3. 実運用耐性
4. UI
5. パフォーマンス最適化
