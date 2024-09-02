<?php
namespace App\Repositories;

use App\Models\Trip;

class TripRepository
{
    public function create(array $data)
    {
        return Trip::create($data);
    }

    public function update($id, array $data)
    {
        return Trip::where('id', $id)->update($data);
    }

    public function delete($id)
    {
        return Trip::destroy($id);
    }

    public function find($id)
    {
        return Trip::find($id);
    }

    public function allByUser($userId)
    {
        return Trip::where('user_id', $userId)->get();
    }
}
