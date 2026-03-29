<?php

use Illuminate\Support\Facades\Schema;

test('all required tables exist after migration', function () {
    expect(Schema::hasTable('couples'))->toBeTrue();
    expect(Schema::hasTable('vows_drafts'))->toBeTrue();
    expect(Schema::hasTable('vows_answers'))->toBeTrue();
    expect(Schema::hasTable('ceremonies'))->toBeTrue();
});

test('users table has role and couple_id columns', function () {
    expect(Schema::hasColumn('users', 'role'))->toBeTrue();
    expect(Schema::hasColumn('users', 'couple_id'))->toBeTrue();
});
