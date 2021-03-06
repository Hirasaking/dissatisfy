<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Model\Article;
use App\Http\Requests\UsersRequest;
use Carbon\Carbon;
use Validator;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = (new Article)->getArticleList();
        return view('article.index')->with('articles', $articles);
    }

    // 人気のある投稿ランキング
    public function rank()
    {
        $articles = (new Article)->getArticleRankList();
        return view('article.rank', ['articles'=>$articles]);
    }

    // 検索機能関連
    public function search(){
        return view('article.search');
    }

    // 検索結果
    public function searchresult(Request $request){
        $keyword = $request->keyword;

        $articles = Article::where('body', 'like', '%' . $keyword . '%')
                ->orWhere('job', 'like', '%' . $keyword . '%')
                ->get();
        $data = Article::paginate(10);
        return view('article.result', ['articles'=>$articles]);
    }

    // 投稿関連機能
    public function create(){
        // ユーザ情報の取得 今は使ってない
        $user = Auth::user();
        return view('article.create');
    }

    public function confirm(UsersRequest $request){

        // リクエストの内容を元にオブジェクト生成
        $article = new Article($request->all());

        $request->session()->regenerateToken();

        //セッションに追加
        //$request->session()->put('article', $article);

        return view('article.confirm', compact('article'));
    }

    //投稿内容の更新処理
    public function update(UsersRequest $request)
    {
        //リクエスト取得
        $contact = $request->all();

        //戻るボタンからの遷移
        if($request->action === 'back') {
            return redirect()->route('create')->withInput($contact);
            //return redirect()->route('create', compact('contact'));
        }

        //戻る以外なら保存処理準備
        $article = new Article($request->all());
        $request->session()->regenerateToken();

        //DBの更新
        $article->save();

        // //セッションから取得
        // $article = $request->session()->get('article');
        //
         return redirect('article/complete');
    }

    public function complete(Request $request)
    {
        //セッションから取得
        $article = $request->session()->get('article');
        return view('article.complete', compact('article'));
    }

    public function edit(Request $request, $id){
        $article = Article::find($id);
        return view('article.edit',['article'=>$article]);
    }

    public function edit_report(Request $request, $id){
        $article = Article::find($id);
        return view('article.edit_report',['article'=>$article]);
    }

    public function report(Request $request){
        $article = Article::find($request->id);
        $article->report_count += 1;
        $article->save();
        return view('article.update');
    }

    public function show(Request $request, $id) {
        $article = Article::find($id);
        return view('article.show', ['article' => $article]);
    }

    public function delete(Request $request) {
        Article::destroy($request->id);
        return view('article.delete');
    }
}
