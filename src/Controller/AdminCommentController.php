<?php

namespace Controller;

use Model\AdminCommentManager;
use Model\Comment;

/**
 * Class AdminCommentController
 *
 * @package \Controller
 */
class AdminCommentController extends AbstractController
{

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function indexAdminComments()
    {
        $commentsManager = new AdminCommentManager($this->getPdo());
        $comments = $commentsManager->selectAllComments();
        $active = 'comments';

        return $this->twig->render('Admin/AdminComment/indexAdminComment.html.twig', [
            'comments' => $comments,
            'active' => $active,
        ] );
    }

    /**
     * @param int $articleId
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function add(int $articleId)
    {
        $errorConnexion = null;

        if (isset($_SESSION['user'])) {

            if (!empty($_POST['content'])) {

                $CommentManager = new AdminCommentManager($this->getPdo());
                $comment = new Comment();
                $comment->setContent($this->verifyInput($_POST['content']));
                $comment->setArticleId($articleId);
                $comment->setUserId($_SESSION['user']['id']);
                $CommentManager->insert($comment);
                header('Location: /article/' . $articleId);

                }else {

                    $errorConnexion = 'Vous devez ajouter un commentaire.';
                    $return = $_SERVER['HTTP_REFERER'];

                return $this->twig->render('Article/logToComment.html.twig', [
                    'errorConnexion' => $errorConnexion,
                    'return' => $return,
                    'isLogged' => $this->isLogged()]);
                }

        }else {

            $errorConnexion = 'Vous devez être connecté pour commenter ce billet.';
            $return = $_SERVER['HTTP_REFERER'];

            return $this->twig->render('Article/logToComment.html.twig', [
                'errorConnexion' => $errorConnexion,
                'return' => $return]);
        }
    }

    /**
     * To add a report to a specific comment, it is incremental
     * @param $id
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax*
     */
    public function addCommentSignal($id)
    {
        $errorConnexion = null;

        if (isset($_SESSION['user']))
        {
            $commentSignal = new AdminCommentManager($this->getPdo());
            $commentSignal->addSignal($id);
        }else{
            $errorConnexion = 'Vous devez être connecté pour signaler ce billet.';
            $return = $_SERVER['HTTP_REFERER'];

            return $this->twig->render('Article/logToSignal.html.twig', [
                'errorConnexion' => $errorConnexion,
                'return' => $return]);
        }
    }

    /**
     * @param int $id
     */
    public function delete(int $id)
    {
        $commentManager = new AdminCommentManager($this->getPdo());
        $commentManager->delete($id);
    }

    /**
     * Index of all reported comments
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function indexAdminCommentsSignals()
    {
        $commentsSignals = new AdminCommentManager($this->getPdo());
        $shows = $commentsSignals->showSignal();
        $signals = $commentsSignals->countSignal();

        return $this->twig->render('Admin/AdminComment/showCommentSignal.html.twig', [
            'comments' => $shows,
            'signals' => $signals
        ]);
    }

    /**
     * delete reports if this is not justified
     * @param $id
     */
    public function resetSignal($id)
    {
        $commentSignal = new AdminCommentManager($this->getPdo());
        $commentSignal->resetSignal($id);
    }

}
