using DataCenter.Data.Base;

using System;
using System.ComponentModel.DataAnnotations;

namespace DataCenter.Data.Models
{
    public class User : BaseEntity, ICreatedDate, IUpdatedDate
    {
        [Required]
        public string Name { get; set; }

        [Required]
        public string FullName { get; set; }

        public string Password { get; set; }

        public string Email { get; set; }

        public string Group { get; set; }

        [Required]
        public int Bonus { get; set; }

        [Required]
        public int MinutesPlayed { get; set; }

        [Required]
        public bool Vip { get; set; }

        [Required]
        public bool Administrator { get; set; }

        [Required]
        public bool Builder { get; set; }

        public virtual UserBanRecord BanRecord { get; set; }

        public DateTime JoinedDate { get; set; }

        public DateTime LeftDate { get; set; }

        public DateTime CreatedDate { get; set; }

        public DateTime UpdatedDate { get; set; }
    }
}