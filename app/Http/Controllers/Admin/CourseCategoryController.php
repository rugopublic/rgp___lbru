<?php

declare(strict_types=1);
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\CourseCategoryRepositoryInterface;
use App\Http\Requests\Admin\Form\Category\SaveRequest;
use Illuminate\Http\Request;
final class CourseCategoryController extends Controller
{

    /**
     * Initializes the course category controller instance and wires its required dependencies.
     *
     * @param CourseCategoryRepositoryInterface $repo
     * @return void
     */
    public function __construct(private readonly CourseCategoryRepositoryInterface $repo){}

    /**
     * Builds the listing response and loads selector/filter data.
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request){$categories=$this->repo->paginateForAdmin($request->only(['search','deleted']));return view('admin.courses.categories.index',compact('categories'));}

    /**
     * Prepares the create flow for the requested resource.
     * @return mixed
     */
    public function create(){return redirect()->route('admin.courses.categories.index')->with('open_create_modal','courseCategoryCreateModal');}

    /**
     * Accepts validated input and delegates creation to the domain service or repository.
     *
     * @param SaveRequest $request
     * @return mixed
     */
    public function store(SaveRequest $request){$category=$this->repo->create(['name'=>$request->validated('name'),'description'=>$request->validated('description'),'is_active'=>$request->boolean('is_active',true)]);return response()->json(['success'=>true,'redirect'=>route('admin.courses.categories.edit',$category)]);}

    /**
     * Loads the resource and prepares its edit view.
     *
     * @param string $category
     * @return mixed
     */
    public function edit(string $category){$category=$this->repo->find($category,true)??abort(404);return view('admin.courses.categories.edit',compact('category'));}

    /**
     * Accepts validated input and delegates the update to the domain service or repository.
     *
     * @param SaveRequest $request
     * @param string $category
     * @return mixed
     */
    public function update(SaveRequest $request,string $category){$model=$this->repo->find($category,true)??abort(404);$this->repo->update($model,$request->validated());return response()->json(['success'=>true,'redirect'=>route('admin.courses.categories.edit',$model)]);}

    /**
     * Deletes the requested resource through the repository or domain service.
     *
     * @param string $category
     * @return mixed
     */
    public function destroy(string $category){$model=$this->repo->find($category,true)??abort(404);$this->repo->delete($model);return response()->json(['success'=>true,'redirect'=>route('admin.courses.categories.index')]);}

    /**
     * Restores the requested soft-deleted resource.
     *
     * @param string $category
     * @return mixed
     */
    public function restore(string $category){$model=$this->repo->find($category,true)??abort(404);$this->repo->restore($model);return response()->json(['success'=>true,'redirect'=>route('admin.courses.categories.index')]);}

    /**
     * Returns selector options for the requested resource type.
     *
     * @param Request $request
     * @return mixed
     */
    public function options(Request $request){return response()->json($this->repo->options($request->query('q'),$request->query('id')));}
}
