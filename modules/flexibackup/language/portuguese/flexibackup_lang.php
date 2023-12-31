<?php

# Version 1.0.0
$key = 'flexibackup';
$lang[$key]     = 'Flexi Backup';
$lang[$key.'_backup_restore'] = 'Flexi Backup e Restauração';
$lang[$key.'_now'] = 'Backup Agora';
$lang[$key.'_perform_a_backup'] = 'Realizar um backup';
$lang[$key.'_take-a-new-backup'] = 'Realizar um backup';
$lang[$key.'_include_database_in_the_backup'] = 'Incluir o banco de dados no backup';
$lang[$key.'_database_backup_info'] = 'Todas as suas tabelas de banco de dados serão copiadas';
$lang[$key.'_include_file_in_the_backup'] = 'Incluir seus arquivos no backup';
$lang[$key.'_files_backup_info'] = 'Seus arquivos serão copiados';
$lang[$key.'_existing_backups'] = 'Backups existentes';
$lang[$key.'_settings'] = 'Configurações';
$lang[$key.'_next_scheduled_backup'] = 'Próximo backup agendado';
$lang[$key.'_files_backup_schedule'] = 'Agendamento de backup de arquivos:';
$lang[$key.'_database_backup_schedule'] = 'Agendamento de backup de banco de dados:';
$lang[$key.'_type_manual'] = 'Manual';
$lang[$key.'_type_every_two_hours'] = 'A cada duas horas';
$lang[$key.'_type_every_four_hours'] = 'A cada quatro horas';
$lang[$key.'_type_every_eight_hours'] = 'A cada oito horas';
$lang[$key.'_type_every_twelve_hours'] = 'A cada doze horas';
$lang[$key.'_type_daily'] = 'Diariamente';
$lang[$key.'_type_weekly'] = 'Semanalmente';
$lang[$key.'_type_fortnightly'] = 'Quinzenalmente';
$lang[$key.'_type_monthly'] = 'Mensalmente';
$lang[$key.'_choose_your_remote_storage'] = 'Escolha seu armazenamento remoto (toque em um ícone para selecionar ou desmarcar)';
$lang[$key.'_ftp_storage'] = 'FTP';
$lang[$key.'_s3_storage'] = 'Amazon S3';
$lang[$key.'_email'] = 'Email';
$lang[$key.'_email_address'] = 'Endereço de e-mail';
$lang[$key.'_email_note'] = 'Esteja ciente de que os servidores de e-mail tendem a ter limites de tamanho; normalmente em torno de 10-20 MB; backups maiores que quaisquer limites provavelmente não chegarão.';
$lang[$key.'_sftp_storage'] = 'SFTP/SCP';
$lang[$key.'_include_in_files_backup'] = 'Local';
$lang[$key.'_include_in_files_backup'] = 'Incluir no backup de arquivos:';
$lang[$key.'_modules'] = 'Modules';
$lang[$key.'_application'] = 'Application';
$lang[$key.'_uploads'] = 'Uploads';
$lang[$key.'_assets'] = 'Assets';
$lang[$key.'_system'] = 'System';
$lang[$key.'_resources'] = 'Resources';
$lang[$key.'_media'] = 'Media';
$lang[$key.'-save-changes'] = 'Salvar alterações';
$lang[$key.'_auto_backup_options_updated'] = 'Configurações salvas com sucesso';
$lang[$key.'_ftp_server'] = 'Servidor FTP';
$lang[$key.'_ftp_user'] = 'Usuário/Login FTP';
$lang[$key.'_ftp_password'] = 'Senha FTP';
$lang[$key.'_ftp_path'] = 'Caminho FTP (Precisa existir e ser gravável)';
$lang[$key.'_s3_description'] = 'Obtenha sua chave de acesso e chave secreta no console da AWS, em seguida, escolha um nome de bucket (globalmente exclusivo - todos os usuários da Amazon S3) 
nome do bucket (letras e números) (e opcionalmente um caminho) para usar para armazenamento.';
$lang[$key.'_sftp_server'] = 'Servidor SFTP/SCP';
$lang[$key.'_sftp_user'] = 'Usuário SFTP';
$lang[$key.'_sftp_password'] = 'Senha SFTP';
$lang[$key.'_sftp_path'] = 'Caminho SFTP (Precisa existir e ser gravável)';
$lang[$key.'_s3_access_key'] = 'Chave de acesso Amazon S3';
$lang[$key.'_s3_secret_key'] = 'Chave secreta Amazon S3';
$lang[$key.'_s3_location'] = 'Nome do bucket Amazon S3';
$lang[$key.'_s3_region'] = 'Região Amazon S3 ex: us-east-1';
$lang[$key.'_back_up_now_note'] = "Suas configurações salvas afetam o que é copiado, você pode atualizá-las <a href='".admin_url('flexibackup/settings')."'><span class='bold'> aqui </span></a>";
$lang[$key.'_time_now'] = 'Hora agora';
$lang[$key.'_nothing_currently_scheduled'] = 'Nada agendado no momento';
$lang[$key.'_files'] = "Arquivos";
$lang[$key.'_database'] = "Banco de dados";
$lang[$key.'_successful'] = "Backup realizado com sucesso";
$lang[$key.'_unsuccessful'] = "Falha no backup, verifique suas configurações e tente novamente";
$lang[$key.'_backup_name_prefix'] = "Prefixo do nome do arquivo de backup";
$lang[$key.'_include_others'] = "Outros (Arquivos raiz como index.php, .htaccess, robots.txt, package.xml e.t.c)";
$lang[$key.'_date'] = "Data do backup";
$lang[$key.'_backup_data_click'] = "Dados de backup (clique para baixar) ";
$lang[$key.'_log_file'] = "Arquivo de log";
$lang[$key.'_download_log_file'] = "Baixar arquivo de log";
$lang[$key.'_download_to_your_computer'] = "Baixar para o seu computador";
$lang[$key.'_delete_from_your_webserver'] = "Excluir do seu servidor web";
$lang[$key.'_browse_contents'] = "Navegar pelo conteúdo";
$lang[$key.'_file_ready_actions'] = "Ações de arquivo pronto";
$lang[$key.'_webdav_storage'] = "WebDAV";
$lang[$key.'_webdav_password'] = "Senha WebDAV";
$lang[$key.'_webdav_username'] = "Nome de usuário WebDAV";
$lang[$key.'_webdav_base_uri'] = "URI base WebDAV";
$lang[$key.'_upload_to_remote'] = "Enviar para armazenamento remoto";
$lang[$key.'_uploaded_to_remote_storage'] = "Enviado para armazenamento remoto";
$lang[$key.'_restore'] = "Restaurar";
$lang[$key.'_restore_files_from'] = "Restauração - Restaurar arquivos de ";
$lang[$key.'_restore_warning'] = "A restauração substituirá os diretórios de conteúdo do Application, Modules, Uploads, Resources, System, Media, Assets, Database e/ou Others deste site (de acordo com o que está contido no conjunto de backup e sua seleção).";
$lang[$key.'_view_log'] = "Ver log";
$lang[$key.'_choose_componet_to_restore'] = "Escolha os componentes para restaurar:";
$lang[$key.'_restore_db_warning'] = "<b>Fazer backup do banco de dados atual (opcional, mas recomendado): </b> Antes de prosseguir com a restauração do banco de dados, é uma boa prática fazer backup do banco de dados atual caso você precise reverter quaisquer alterações.";
$lang[$key.'_log_file_not_found'] = "Nenhum arquivo de log encontrado";
$lang[$key.'_could_not_donwload_file'] = "Não foi possível baixar o arquivo";
$lang[$key.'_file_removed_successfully'] = "Arquivo removido com sucesso";
$lang[$key.'_could_not_remove_backup'] = "Não foi possível remover o backup.";
$lang[$key.'_files_uploaded_to'] = "Arquivos enviados para ";
$lang[$key.'_successfully'] = " com sucesso";
$lang[$key.'_could_not_complete_remote_backup'] = "Não foi possível concluir o backup remoto deste backup. ";
$lang[$key.'_no_remote_storage_selected'] = "Selecione uma opção de armazenamento remoto nas configurações. ";
$lang[$key.'_backup_restored_successfully'] = "Backup restaurado com sucesso ";
$lang[$key.'_could_not_restore_backup'] = "Não foi possível restaurar o backup.  ";
$lang[$key.'_at_least_one_file_type_to_restore'] = "Selecione pelo menos um tipo de arquivo para restaurar.";
$lang[$key.'_auto_backup_to_remote_enabled'] = "Upload automático de backup agendado para armazenamento remoto";
$lang[$key.'_choose_the_time_of_your_scheduled_backup'] = "Escolha a hora do seu backup agendado";




