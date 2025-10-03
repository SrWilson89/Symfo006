<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250930100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE clientes (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, direccion VARCHAR(255) NOT NULL, codigo_postal VARCHAR(6) NOT NULL, pais VARCHAR(255) NOT NULL, provincia VARCHAR(255) NOT NULL, localidad VARCHAR(255) NOT NULL, notas VARCHAR(255) DEFAULT NULL, cif VARCHAR(255) NOT NULL, fech_creacion DATETIME DEFAULT NULL, modificacion DATETIME DEFAULT NULL, activo TINYINT(1) DEFAULT NULL, testat DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer_mail_settings (id INT AUTO_INCREMENT NOT NULL, cliente_id INT NOT NULL, mail_domain VARCHAR(255) DEFAULT NULL, smtp_host VARCHAR(255) DEFAULT NULL, smtp_port INT DEFAULT NULL, smtp_encryption VARCHAR(32) DEFAULT NULL, smtp_username VARCHAR(255) DEFAULT NULL, smtp_password VARCHAR(255) DEFAULT NULL, smtp_auth_mode VARCHAR(64) DEFAULT NULL, from_email VARCHAR(255) DEFAULT NULL, from_name VARCHAR(255) DEFAULT NULL, reply_to_email VARCHAR(255) DEFAULT NULL, spf_record LONGTEXT DEFAULT NULL, dkim_domain VARCHAR(255) DEFAULT NULL, dkim_selector VARCHAR(63) DEFAULT \'mail\', dkim_key_algorithm VARCHAR(16) DEFAULT \'rsa\', dkim_key_bits INT DEFAULT 2048, dkim_public_key LONGTEXT DEFAULT NULL, dkim_private_key LONGTEXT DEFAULT NULL, dkim_private_key_path VARCHAR(255) DEFAULT NULL, dmarc_policy VARCHAR(16) DEFAULT NULL, dmarc_rua VARCHAR(255) DEFAULT NULL, dmarc_ruf VARCHAR(255) DEFAULT NULL, dmarc_subdomain_policy VARCHAR(16) DEFAULT NULL, dmarc_adkim VARCHAR(1) DEFAULT NULL, dmarc_aspf VARCHAR(1) DEFAULT NULL, dmarc_pct INT DEFAULT NULL, mail_auth_updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_5CF7118EDE734E51 (cliente_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE detalles (id INT AUTO_INCREMENT NOT NULL, producto_id INT NOT NULL, precio NUMERIC(10, 2) NOT NULL, iva NUMERIC(10, 2) NOT NULL, cantidad DOUBLE PRECISION DEFAULT NULL, INDEX IDX_3D57C6DB7645698E (producto_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE estados (id INT AUTO_INCREMENT NOT NULL, cliente_id INT DEFAULT NULL, nombre VARCHAR(255) NOT NULL, color VARCHAR(255) NOT NULL, fin TINYINT(1) DEFAULT NULL, INDEX IDX_222B2128DE734E51 (cliente_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hilos (id INT AUTO_INCREMENT NOT NULL, tarea_id INT NOT NULL, usuario_id INT NOT NULL, notas VARCHAR(255) NOT NULL, fecha_creacion DATETIME DEFAULT NULL, modificacion DATETIME DEFAULT NULL, INDEX IDX_43EA16B26D5BDFE1 (tarea_id), INDEX IDX_43EA16B2DB38439E (usuario_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        //  !!! LÍNEA DE PRESUPUESTOS CORREGIDA !!!
        $this->addSql('CREATE TABLE presupuestos (id INT AUTO_INCREMENT NOT NULL, detalle_id INT DEFAULT NULL, direccion VARCHAR(255) NOT NULL, fecha DATETIME NOT NULL, tipo TINYINT(1) NOT NULL, numref INT DEFAULT NULL, estado VARCHAR(255) DEFAULT NULL, INDEX IDX_4CF2F0D9EA59ED2 (detalle_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE productos (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) DEFAULT NULL, precio NUMERIC(10, 2) NOT NULL, iva NUMERIC(10, 2) NOT NULL, fechacreacion DATETIME NOT NULL, modificacion DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sent_email (id INT AUTO_INCREMENT NOT NULL, sender_id INT DEFAULT NULL, from_email VARCHAR(255) NOT NULL, subject VARCHAR(255) NOT NULL, to_recipients LONGTEXT DEFAULT NULL, cc_recipients LONGTEXT DEFAULT NULL, bcc_recipients LONGTEXT DEFAULT NULL, body_text LONGTEXT DEFAULT NULL, body_html LONGTEXT DEFAULT NULL, sent_at DATETIME NOT NULL, success TINYINT(1) NOT NULL, error_message LONGTEXT DEFAULT NULL, INDEX IDX_E92EE5FCF624B39D (sender_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tareas (id INT AUTO_INCREMENT NOT NULL, estado_id INT DEFAULT NULL, usuario_id INT DEFAULT NULL, fecha_creacion DATETIME NOT NULL, modificacion DATETIME NOT NULL, titulo VARCHAR(255) DEFAULT NULL, notas VARCHAR(255) DEFAULT NULL, fechafin DATETIME DEFAULT NULL, cliente INT DEFAULT NULL, INDEX IDX_BFE3AB359F5A440B (estado_id), INDEX IDX_BFE3AB35DB38439E (usuario_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, cliente_id INT DEFAULT NULL, nombre VARCHAR(255) NOT NULL, apellidos VARCHAR(255) DEFAULT NULL, nif VARCHAR(10) DEFAULT NULL, telefono VARCHAR(9) DEFAULT NULL, fecha_creacion DATETIME DEFAULT NULL, modificacion DATETIME DEFAULT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, super INT NOT NULL, notas VARCHAR(255) DEFAULT NULL, activo TINYINT(1) DEFAULT NULL, imagen VARCHAR(255) DEFAULT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', is_online TINYINT(1) NOT NULL, last_login DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D649DE734E51 (cliente_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE customer_mail_settings ADD CONSTRAINT FK_5CF7118EDE734E51 FOREIGN KEY (cliente_id) REFERENCES clientes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE detalles ADD CONSTRAINT FK_3D57C6DB7645698E FOREIGN KEY (producto_id) REFERENCES productos (id)');
        $this->addSql('ALTER TABLE estados ADD CONSTRAINT FK_222B2128DE734E51 FOREIGN KEY (cliente_id) REFERENCES clientes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE hilos ADD CONSTRAINT FK_43EA16B26D5BDFE1 FOREIGN KEY (tarea_id) REFERENCES tareas (id)');
        $this->addSql('ALTER TABLE hilos ADD CONSTRAINT FK_43EA16B2DB38439EDB38439E FOREIGN KEY (usuario_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE presupuestos ADD CONSTRAINT FK_4CF2F0D9EA59ED2 FOREIGN KEY (detalle_id) REFERENCES detalles (id)');
        $this->addSql('ALTER TABLE sent_email ADD CONSTRAINT FK_E92EE5FCF624B39D FOREIGN KEY (sender_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE tareas ADD CONSTRAINT FK_BFE3AB359F5A440B FOREIGN KEY (estado_id) REFERENCES estados (id)');
        $this->addSql('ALTER TABLE tareas ADD CONSTRAINT FK_BFE3AB35DB38439E FOREIGN KEY (usuario_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649DE734E51DE734E51 FOREIGN KEY (cliente_id) REFERENCES clientes (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_mail_settings DROP FOREIGN KEY FK_5CF7118EDE734E51');
        $this->addSql('ALTER TABLE detalles DROP FOREIGN KEY FK_3D57C6DB7645698E');
        $this->addSql('ALTER TABLE estados DROP FOREIGN KEY FK_222B2128DE734E51');
        $this->addSql('ALTER TABLE hilos DROP FOREIGN KEY FK_43EA16B26D5BDFE1');
        $this->addSql('ALTER TABLE hilos DROP FOREIGN KEY FK_43EA16B2DB38439EDB38439E');
        $this->addSql('ALTER TABLE presupuestos DROP FOREIGN KEY FK_4CF2F0D9EA59ED2');
        $this->addSql('ALTER TABLE sent_email DROP FOREIGN KEY FK_E92EE5FCF624B39D');
        $this->addSql('ALTER TABLE tareas DROP FOREIGN KEY FK_BFE3AB359F5A440B');
        $this->addSql('ALTER TABLE tareas DROP FOREIGN KEY FK_BFE3AB35DB38439E');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649DE734E51DE734E51');
        $this->addSql('DROP TABLE clientes');
        $this->addSql('DROP TABLE customer_mail_settings');
        $this->addSql('DROP TABLE detalles');
        $this->addSql('DROP TABLE estados');
        $this->addSql('DROP TABLE hilos');
        $this->addSql('DROP TABLE presupuestos');
        $this->addSql('DROP TABLE productos');
        $this->addSql('DROP TABLE sent_email');
        $this->addSql('DROP TABLE tareas');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}