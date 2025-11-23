<?php
// FILE: /app/controllers/BrandController.php

class BrandController extends Controller {

    public function index() {
        $this->requireAuth();

        $brandModel = $this->model('BrandKit');
        $brandKits = $brandModel->getAllWithDecoded();

        $this->view->render('brand/index', [
            'user' => $this->getCurrentUser(),
            'brand_kits' => $brandKits
        ]);
    }

    public function create() {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();

            $name = $_POST['name'] ?? '';
            $voiceTone = $_POST['voice_tone'] ?? 'professional';
            $colors = isset($_POST['colors']) ? array_filter($_POST['colors']) : [];
            $fonts = isset($_POST['fonts']) ? array_filter($_POST['fonts']) : [];

            $errors = [];

            if (!ValidationHelper::required($name)) {
                $errors[] = 'Brand kit name is required';
            }

            if (empty($errors)) {
                $brandModel = $this->model('BrandKit');
                $brandKitId = $brandModel->createBrandKit([
                    'tenant_id' => $this->getCurrentTenantId(),
                    'name' => $name,
                    'colors' => $colors,
                    'fonts' => $fonts,
                    'voice_tone' => $voiceTone
                ]);

                $this->logActivity('brand_kit', $brandKitId, 'created', 'Created brand kit');

                $this->redirect('/brand');
                return;
            }

            $this->view->render('brand/create', [
                'user' => $this->getCurrentUser(),
                'errors' => $errors,
                'old' => $_POST,
                'csrf_token' => $this->generateCSRF()
            ]);
        } else {
            $this->view->render('brand/create', [
                'user' => $this->getCurrentUser(),
                'csrf_token' => $this->generateCSRF()
            ]);
        }
    }

    public function edit($id) {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);

        $brandModel = $this->model('BrandKit');
        $brandKit = $brandModel->getBrandKitWithDecoded($id);

        if (!$brandKit) {
            http_response_code(404);
            $this->view->render('errors/404', ['message' => 'Brand kit not found']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();

            $name = $_POST['name'] ?? '';
            $voiceTone = $_POST['voice_tone'] ?? 'professional';
            $colors = isset($_POST['colors']) ? array_filter($_POST['colors']) : [];
            $fonts = isset($_POST['fonts']) ? array_filter($_POST['fonts']) : [];

            $brandModel->updateBrandKit($id, [
                'name' => $name,
                'colors' => $colors,
                'fonts' => $fonts,
                'voice_tone' => $voiceTone
            ]);

            $this->logActivity('brand_kit', $id, 'updated', 'Updated brand kit');

            $this->redirect('/brand');
        } else {
            $this->view->render('brand/edit', [
                'user' => $this->getCurrentUser(),
                'brand_kit' => $brandKit,
                'csrf_token' => $this->generateCSRF()
            ]);
        }
    }

    public function delete($id) {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);
        $this->validateCSRF();

        $brandModel = $this->model('BrandKit');
        $brandModel->delete($id);

        $this->logActivity('brand_kit', $id, 'deleted', 'Deleted brand kit');

        $this->json(['status' => 'success']);
    }
}
