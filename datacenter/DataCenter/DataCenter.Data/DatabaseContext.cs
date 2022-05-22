using Microsoft.EntityFrameworkCore;
using DataCenter.Data.Models;
using DataCenterDatabase = DataCenter.Data.Database;
using DataCenter.Data.Models.Audit;

namespace DataCenter.Data
{
    public class DatabaseContext : DbContext
    {
        public DbSet<Credentials> Credentials { get; set; }

        public DbSet<User> Users { get; set; }

        public DbSet<UserSettings> UserSettings { get; set; }

        public DbSet<MapPoint> MapPoints { get; set; }

        public DbSet<FloatingText> FloatingTexts { get; set; }

        public DbSet<ExecutedCommandAuditRecord> ExecutedCommandAuditRecords { get; set; }

        public DbSet<ChatMessageAuditRecord> ChatMessageAuditRecords { get; set; }

        public DbSet<UserTrafficAuditRecord> UserTrafficAuditRecords { get; set; }

        public DbSet<UserBanRecord> UserBanRecords { get; set; }

        protected override void OnConfiguring(DbContextOptionsBuilder optionsBuilder)
        {
            if(!DataCenterDatabase.IsInitialized)
            {
                DataCenterDatabase.Initialize();
            }

            optionsBuilder.UseLazyLoadingProxies()
                .UseMySQL(DataCenterDatabase.Builder.ConnectionString);
        }

        protected override void OnModelCreating(ModelBuilder modelBuilder)
        {
            base.OnModelCreating(modelBuilder);

            ConfigureRelationships(modelBuilder);
        }

        private void ConfigureRelationships(ModelBuilder modelBuilder)
        {
            modelBuilder.Entity<User>()
                .HasOne(user => user.BanRecord);
        }
    }
}
