# MoneyMoney Sync to YNAB

Can Sync transactions from MoneyMoney directly to YNAB (Web). Must be run on the same Mac as MoneyMoney is running.


# Install

```
composer install
```

# Setup

```
php bin/console app:setup
```

Complete the interactive menu and select which MoneyMoney Accounts to Sync to which YNAB accounts.
Create a new YNAB API token here: https://app.youneedabudget.com/settings/developer

# Sync
```
php bin/console app:sync
```
