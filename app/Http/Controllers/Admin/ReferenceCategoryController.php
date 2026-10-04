<?php

declare(strict_types=1);
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ReferenceCategoryRepositoryInterface;
use App\Http\Requests\Admin\Form\Category\SaveRequest;
use Illuminate\Http\Request;
final class ReferenceCategoryController extends Controller
{

    /**
     * Initializes the reference category controller instance and wires its required dependencies.
     *
     * @param ReferenceCategoryRepositoryInterface $repo
     * @return void
     */
    public function __construct(private readonly ReferenceCategoryRepositoryInterface $repo){}

    /**
     * Builds the listing response and loads selector/filter data.
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request){ $items=$this->repo->paginateForAdmin($request->only(['search','deleted'])); return view('admin.reference/items.categories.index',compact('items')); }

    /**
     * Prepares the create flow for the requested resource.
     * @return mixed
     */
    public function create(){ return redirect()->route('admin.reference.categories.index')->with('open_create_modal','referenceCategoryCreateModal'); }

    /**
     * Accepts validated input and delegates creation to the domain service or repository.
     *
     * @param SaveRequest $request
     * @return mixed
     */
    public function store(SaveRequest $request){ $m=$this->repo->create(['name'=>$request->validated('name'),'description'=>$request->validated('description'),'is_active'=>$request->boolean('is_active',true)]); return response()->json(['success'=>true,'redirect'=>route('admin.reference.categories.edit',$m)]); }

    /**
     * Loads the resource and prepares its edit view.
     *
     * @param string $category
     * @return mixed
     */
    public function edit(string $category){ $category=$this->repo->find($category,true) ?? abort(404); return view('admin.reference/items.categories.edit',compact('category')); }

    /**
     * Accepts validated input and delegates the update to the domain service or repository.
     *
     * @param SaveRequest $request
     * @param string $category
     * @return mixed
     */
    public function update(SaveRequest $request,string $category){ $m=$this->repo->find($category,true)??abort(404);$this->repo->update($m,$request->validated());return response()->json(['success'=>true,'redirect'=>route('admin.reference.categories.edit',$m)]); }

    /**
     * Deletes the requested resource through the repository or domain service.
     *
     * @param string $category
     * @return mixed
     */
    public function destroy(string $category){ $m=$this->repo->find($category,true)??abort(404);$this->repo->delete($m);return response()->json(['success'=>true,'redirect'=>route('admin.reference.categories.index')]); }

    /**
     * Restores the requested soft-deleted resource.
     *
     * @param string $category
     * @return mixed
     */
    public function restore(string $category){ $m=$this->repo->find($category,true)??abort(404);$this->repo->restore($m);return response()->json(['success'=>true,'redirect'=>route('admin.reference.categories.index')]); }

    /**
     * Returns selector options for the requested resource type.
     *
     * @param Request $request
     * @return mixed
     */
    public function options(Request $request){return response()->json($this->repo->options($request->query('q'),$request->query('id')));}
}
